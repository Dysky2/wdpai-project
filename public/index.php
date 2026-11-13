<?php

declare(strict_types=1);

// ── Autoloader ────────────────────────────────────────────────────────────────
// When vendor/ is available (Docker / composer install) use the PSR-4 autoloader.
// Until then fall back to the legacy require_once chain so the app still boots.
$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    // Legacy fallback — will be removed once Docker is always up
    require_once dirname(__DIR__) . '/src/controllers/AppController.php';
    require_once dirname(__DIR__) . '/src/controllers/DefaultController.php';
    require_once dirname(__DIR__) . '/src/controllers/SecurityController.php';
    require_once dirname(__DIR__) . '/src/controllers/AboutController.php';
    require_once dirname(__DIR__) . '/src/controllers/ProjectController.php';
}

require_once dirname(__DIR__) . '/Routing.php';

// ── Routing ───────────────────────────────────────────────────────────────────
$routing = new Routing();

if (is_file($vendorAutoload)) {
    // PSR-4 namespaced controllers (used when vendor/ autoloader is available)
    $routing->get('/', \App\Controller\HomeController::class, 'index');
    $routing->get('/login', \App\Controller\AuthController::class, 'login');
    $routing->post('/login', \App\Controller\AuthController::class, 'login');
    $routing->get('/register', \App\Controller\AuthController::class, 'register');
    $routing->post('/register', \App\Controller\AuthController::class, 'register');
    $routing->get('/profile/{id}', \App\Controller\AuthController::class, 'profile');
} else {
    // Legacy routes (global-namespace controllers)
    $routing->get('/', DefaultController::class, 'index');
    $routing->get('/login', SecurityController::class, 'login');
    $routing->get('/register', SecurityController::class, 'register');
    $routing->get('/profil/{id}', SecurityController::class, 'profil');
    $routing->get('/about', AboutController::class, 'index');
    $routing->get('/projects', ProjectController::class, 'index');
    $routing->get('/projects/{id}', ProjectController::class, 'show');
}

// ── Dispatch ──────────────────────────────────────────────────────────────────
$routeFound = $routing->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
);

if (!$routeFound) {
    http_response_code(404);
    if (is_file($vendorAutoload)) {
        (new \App\Controller\HomeController())->notFound()->send();
    } else {
        (new DefaultController())->notFound();
    }
}
