<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Http\Request;
use App\Http\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$router->get('/', HomeController::class, 'index');
$router->get('/login', AuthController::class, 'login');
$router->post('/login', AuthController::class, 'login');
$router->get('/register', AuthController::class, 'register');
$router->post('/register', AuthController::class, 'register');
$router->get('/profile/{id}', AuthController::class, 'profile');

$router->dispatch(Request::fromGlobals())->send();
