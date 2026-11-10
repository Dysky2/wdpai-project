<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Router;

final class Routing
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function get(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->router->get($path, $controller, $action, $middlewares);
    }

    public function post(string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->router->post($path, $controller, $action, $middlewares);
    }

    public function dispatch(string $method, string $uri): bool
    {
        $request = Request::fromGlobals();
        $response = $this->router->dispatch($request);

        if ($response->getStatusCode() === 404 && $response->getContent() === '404 Not Found') {
            return false;
        }

        $response->send();
        return true;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
