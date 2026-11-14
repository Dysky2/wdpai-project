<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\IncidentController;
use App\Controller\StandingsController;
use App\Controller\StewardController;
use App\Http\Router;

return static function (Router $router): void {
    $router->get('/', HomeController::class, 'index');

    $router->get('/login', AuthController::class, 'login');
    $router->post('/login', AuthController::class, 'login');
    $router->get('/register', AuthController::class, 'register');
    $router->post('/register', AuthController::class, 'register');
    $router->post('/logout', AuthController::class, 'logout');

    $router->get('/incidents', IncidentController::class, 'index');
    // Static /incidents/create must be registered before /incidents/{id}.
    $router->get('/incidents/create', IncidentController::class, 'create');
    $router->post('/incidents', IncidentController::class, 'create');
    $router->get('/incidents/{id}', IncidentController::class, 'show');

    $router->get('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate');
    $router->post('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate');

    $router->get('/standings', StandingsController::class, 'index');
};
