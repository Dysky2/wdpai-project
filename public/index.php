<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Router;

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

$container = require $root . '/config/container.php';
$router = $container->get(Router::class);
(require $root . '/config/routes.php')($router);

$router->dispatch(Request::fromGlobals())->send();
