<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\IncidentController;
use App\Controller\StandingsController;
use App\Controller\StewardController;
use App\Core\Container;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Model\Role;
use App\Service\AuthService;

return static function (Router $router, Container $container): void {
    // Every POST needs a valid CSRF token (form field or X-CSRF-Token header).
    $router->use(CsrfMiddleware::class);

    // Roles are assigned per route; ownership of a single case is checked in IncidentService::getForUser.
    $authService = $container->get(AuthService::class);
    $auth = [AuthMiddleware::class];
    $principal = [AuthMiddleware::class, RoleMiddleware::for($authService, Role::TEAM_PRINCIPAL)];
    $steward = [AuthMiddleware::class, RoleMiddleware::for($authService, Role::FIA_STEWARD)];

    $router->get('/', HomeController::class, 'index');

    $router->get('/login', AuthController::class, 'login');
    $router->post('/login', AuthController::class, 'login');
    $router->post('/logout', AuthController::class, 'logout');

    $router->get('/incidents', IncidentController::class, 'index', $auth);
    // Static /incidents/create must be registered before /incidents/{id}.
    $router->get('/incidents/create', IncidentController::class, 'create', $principal);
    $router->post('/incidents', IncidentController::class, 'create', $principal);
    $router->get('/incidents/{id}', IncidentController::class, 'show', $auth);

    $router->get('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate', $steward);
    $router->post('/stewards/adjudicate/{id}', StewardController::class, 'adjudicate', $steward);

    $router->get('/standings', StandingsController::class, 'index', $auth);
};
