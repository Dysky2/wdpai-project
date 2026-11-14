<?php

declare(strict_types=1);

namespace App\Http;

use App\Controller\ErrorController;
use App\Core\Container;
use App\Exception\BadRequestException;
use App\Exception\HttpException;
use App\Exception\NotFoundException;
use App\Middleware\MiddlewareInterface;

/**
 * Matches HTTP method + path against registered routes.
 *
 * Supported path segments:
 *  - static:       /login, /incidents
 *  - path param:   /incidents/{id}  (must be a positive integer, otherwise 400)
 *  - regex group:  /lang/(pl|en)    (a segment wrapped in parentheses is used as a raw regex)
 *
 * Query parameters are not part of the pattern; read them with Request::getQuery().
 * Unknown paths give 404; HttpException thrown by a controller or middleware is rendered
 * as an HTML error page (or JSON error for /api/* paths).
 */
final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly ?Container $container = null,
    ) {
    }

    public function get(string $path, string $controller, string $action, array $middlewares = []): self
    {
        $this->add('GET', $path, $controller, $action, $middlewares);
        return $this;
    }

    public function post(string $path, string $controller, string $action, array $middlewares = []): self
    {
        $this->add('POST', $path, $controller, $action, $middlewares);
        return $this;
    }

    private function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        array $middlewares = [],
    ): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $this->createPattern($path),
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    private function createPattern(string $path): string
    {
        $parts = explode('/', $path);

        foreach ($parts as &$part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                // Captured loosely here; dispatch() validates it as a positive integer (400 otherwise).
                $part = '(?P<' . trim($part, '{}') . '>[^/]+)';
                continue;
            }

            if (str_starts_with($part, '(') && str_ends_with($part, ')')) {
                continue; // raw regex group, used as-is
            }

            $part = preg_quote($part, '~');
        }
        unset($part);

        return '~^' . implode('/', $parts) . '$~';
    }

    public function dispatch(Request $request): Response
    {
        try {
            return $this->handle($request);
        } catch (HttpException $e) {
            return $this->errorResponse($request, $e->getStatusCode(), $e->getErrorCode(), $e->getMessage());
        } catch (\Throwable $e) {
            error_log($e::class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->errorResponse($request, 500, 'server_error', 'Internal server error');
        }
    }

    private function handle(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $parameters = $this->extractParameters($matches);
            $controllerClass = $route['controller'];
            $action = $route['action'];

            $coreAction = function (Request $req) use ($controllerClass, $action, $parameters): Response {
                $controller = $this->resolve($controllerClass);

                // Pass the Request as first argument only when the action type-hints it.
                $params = (new \ReflectionMethod($controller, $action))->getParameters();
                $args = [];
                if ($params !== [] && (string) $params[0]->getType() === Request::class) {
                    $args[] = $req;
                }
                $args = array_merge($args, $parameters);

                ob_start();
                $result = $controller->{$action}(...$args);
                $buffered = ob_get_clean();

                if ($result instanceof Response) {
                    return $result;
                }

                return new Response($buffered !== false ? $buffered : '', 200);
            };

            return $this->executeMiddlewareChain($route['middlewares'], $request, $coreAction);
        }

        throw new NotFoundException('Route not found: ' . $method . ' ' . $path);
    }

    /**
     * Turns regex matches into an ordered list of controller arguments.
     * {name} groups become positive ints (400 when invalid); raw regex groups stay strings.
     *
     * @param array<int|string, string> $matches
     * @return list<int|string>
     */
    private function extractParameters(array $matches): array
    {
        $parameters = [];
        $skipNextIndex = false;

        foreach (array_slice($matches, 1, null, true) as $key => $value) {
            if (is_string($key)) {
                $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id === false || !ctype_digit($value)) {
                    throw new BadRequestException('Parameter "' . $key . '" must be a positive integer');
                }
                $parameters[] = $id;
                // PCRE also reports a named group under its numeric index right after the name.
                $skipNextIndex = true;
                continue;
            }

            if ($skipNextIndex) {
                $skipNextIndex = false;
                continue;
            }

            $parameters[] = $value;
        }

        return $parameters;
    }

    private function resolve(string $class): object
    {
        return $this->container !== null && $this->container->has($class)
            ? $this->container->get($class)
            : new $class();
    }

    private function errorResponse(Request $request, int $status, string $code, string $message): Response
    {
        if (str_starts_with($request->getPath(), '/api/')) {
            // Never leak internals of server errors to API clients.
            $publicMessage = $status >= 500 ? 'Internal server error' : $message;
            return new JsonResponse(['error' => ['code' => $code, 'message' => $publicMessage]], $status);
        }

        return $this->resolve(ErrorController::class)->show($status);
    }

    private function executeMiddlewareChain(array $middlewares, Request $request, callable $core): Response
    {
        $runner = array_reduce(
            array_reverse($middlewares),
            function (callable $next, string|MiddlewareInterface $middleware): callable {
                return function (Request $req) use ($middleware, $next): Response {
                    $instance = is_string($middleware) ? $this->resolve($middleware) : $middleware;

                    return $instance->handle($req, $next);
                };
            },
            $core,
        );

        return $runner($request);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
