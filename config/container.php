<?php

declare(strict_types=1);

use App\Core\Container;
use App\Http\Router;

$container = new Container();

// Interfaces are bound to implementations here (e.g. IncidentRepositoryInterface => PdoIncidentRepository).
// Concrete controllers and services are autowired by Container from constructor type hints.
$container->singleton(Router::class, static fn (Container $c): Router => new Router($c));

return $container;
