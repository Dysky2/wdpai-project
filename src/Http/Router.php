<?php

declare(strict_types=1);

namespace App\Http;

use App\Core\Container;
use App\Middleware\MiddlewareInterface;

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
                $parameterName = trim($part, '{}');
                $part = '(?P<' . $parameterName . '>\d+)';
                continue;
            }

            $part = preg_quote($part, '~');
        }
        unset($part);

        return '~^' . implode('/', $parts) . '$~';
    }

    public function dispatch(Request $request): Response
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

            $parameters = array_filter(
                $matches,
                static fn (int|string $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY,
            );

            // Validation: cast numeric params to int
            $parameters = array_map(
                static fn (string $value): int|string =>
                    ctype_digit($value) ? (int) $value : $value,
                $parameters,
            );

            // Build middleware chain
            $middlewares = $route['middlewares'];
            $controllerClass = $route['controller'];
            $action = $route['action'];

            $coreAction = function (Request $req) use ($controllerClass, $action, $parameters): Response {
                $controller = $this->container !== null && $this->container->has($controllerClass)
                    ? $this->container->get($controllerClass)
                    : new $controllerClass();

                // Introspect first parameter: if it typehints Request, inject it.
                // This keeps legacy void controllers (no Request param) working alongside
                // the new namespaced controllers that accept Request as first argument.
                $ref   = new \ReflectionMethod($controller, $action);
                $params = $ref->getParameters();
                $args  = [];
                if (!empty($params) && $params[0]->hasType()) {
                    $typeName = (string) $params[0]->getType();
                    if ($typeName === Request::class || $typeName === 'App\\Http\\Request') {
                        $args[] = $req;
                    }
                }
                $args = array_merge($args, array_values($parameters));

                // If controller action returns a Response object, return it.
                // If it renders directly (legacy/void), capture output in a Response.
                ob_start();
                $result = $controller->{$action}(...$args);
                $buffered = ob_get_clean();

                if ($result instanceof Response) {
                    return $result;
                }

                return new Response($buffered !== false ? $buffered : '', 200);
            };

            return $this->executeMiddlewareChain($middlewares, $request, $coreAction);
        }

        return new Response('404 Not Found', 404);
    }

    private function executeMiddlewareChain(array $middlewares, Request $request, callable $core): Response
    {
        $runner = array_reduce(
            array_reverse($middlewares),
            function (callable $next, string|MiddlewareInterface $middleware): callable {
                return function (Request $req) use ($middleware, $next): Response {
                    $instance = is_string($middleware)
                        ? ($this->container !== null && $this->container->has($middleware)
                            ? $this->container->get($middleware)
                            : new $middleware())
                        : $middleware;

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
