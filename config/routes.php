<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\IncidentController;
use App\Controller\StandingsController;
use App\Controller\StewardController;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

return static function (Router $router): void {
    // Every POST needs a valid CSRF token (form field or X-CSRF-Token header).
    $router->use(CsrfMiddleware::class);

    $auth = [AuthMiddleware::class];

    $router->get('/', HomeController::class, 'index');

    $router->get('/login', AuthController::class, 'login');
    $router->post('/login', AuthController::class, 'login');
    $router->post('/logout', AuthController::class, 'logout');

    $router->get('/incidents', IncidentController::class, 'index', $auth);
    // Static /incidents/create must be registered before /incidents/{id}.
    $router->get('/incidents/create', IncidentController::class, 'create', $auth);
    $router->post('/incidents', IncidentController::class, 'create', $auth);
    $router->get('/incidents/{id}', IncidentController::class, 'show', $auth);

    $router->get('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate', $auth);
    $router->post('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate', $auth);

    $router->get('/standings', StandingsController::class, 'index', $auth);
};
