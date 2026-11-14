<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;

/**
 * AuthController handles the login and registration pages.
 * Until the authentication card is implemented, submitting either form
 * simply continues to the incidents log (no credentials are checked).
 */
final class AuthController extends AbstractController
{
    public function login(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/incidents');
        }

        return $this->render('login', [
            'pageTitle' => 'Logowanie — ApexSteward',
            'pageClass' => 'page--centered',
        ]);
    }

    public function register(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/incidents');
        }

        return $this->render('register', [
            'pageTitle' => 'Rejestracja — ApexSteward',
            'pageClass' => 'page--centered',
        ]);
    }

    public function logout(): Response
    {
        return Response::redirect('/login');
    }
}
