<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;

/**
 * AuthController handles authentication-related pages:
 * login and registration.
 */
final class AuthController extends AbstractController
{
    public function login(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/');
        }

        return $this->render('login', [
            'pageTitle'   => 'Logowanie — ApexSteward',
            'activeRoute' => '/login',
        ]);
    }

    public function register(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/');
        }

        return $this->render('register', [
            'pageTitle'   => 'Rejestracja — ApexSteward',
            'activeRoute' => '/register',
        ]);
    }
}
