<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;

/**
 * AuthController handles authentication-related pages:
 * login, registration and user profile.
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

    /**
     * User profile page. Accepts the user id as a path parameter.
     *
     * @param int $id User identifier extracted from the URL
     */
    public function profile(Request $request, int $id): Response
    {
        // Placeholder data — will be replaced by UserService in a later card
        $name  = 'jan_kowalski';
        $email = 'jan.kowalski@example.com';
        $role  = 'TEAM_PRINCIPAL';

        return $this->render('profil', [
            'pageTitle'    => 'Profil użytkownika — ApexSteward',
            'activeRoute'  => '/profile/' . $id,
            'profilId'     => $id,
            'profilName'   => $name,
            'profilEmail'  => $email,
            'profilRole'   => $role,
        ]);
    }
}
