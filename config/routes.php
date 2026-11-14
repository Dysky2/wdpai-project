<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Http\Router;

return static function (Router $router): void {
    $router->get('/', HomeController::class, 'index');
    $router->get('/login', AuthController::class, 'login');
    $router->post('/login', AuthController::class, 'login');
    $router->get('/register', AuthController::class, 'register');
    $router->post('/register', AuthController::class, 'register');
};
