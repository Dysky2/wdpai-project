<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Service\LoginValidator;
use App\View\ViewHelper;

/**
 * Login and logout. Accounts are created by the seed (there is no registration).
 */
final class AuthController extends AbstractController
{
    public function __construct(
        ViewHelper $view,
        private readonly AuthService $auth,
        private readonly LoginValidator $validator,
    ) {
        parent::__construct($view);
    }

    public function login(Request $request): Response
    {
        if ($this->auth->currentUser() !== null) {
            return Response::redirect('/incidents');
        }

        if (!$request->isPost()) {
            return $this->renderLogin();
        }

        $email = $request->getParsedBody('email');
        $password = $request->getParsedBody('password');
        $email = is_string($email) ? $email : '';
        $password = is_string($password) ? $password : '';

        $errorKeys = $this->validator->validate($email, $password);
        if ($errorKeys !== []) {
            $errors = array_map(fn (string $key): string => $this->view->t($key), $errorKeys);

            return $this->renderLogin($email, null, $errors)->setStatusCode(422);
        }

        $user = $this->auth->attempt($email, $password);
        if ($user === null) {
            return $this->renderLogin($email, $this->view->t('login.invalid_credentials'))->setStatusCode(422);
        }

        $this->auth->login($user);

        return Response::redirect('/incidents');
    }

    public function logout(): Response
    {
        $this->auth->logout();

        return Response::redirect('/login');
    }

    /**
     * @param array<string, string> $errors field => message
     */
    private function renderLogin(string $email = '', ?string $error = null, array $errors = []): Response
    {
        return $this->render('login', [
            'pageTitle' => $this->view->t('login.page_title'),
            'pageClass' => 'page--centered',
            'old' => ['email' => $email],
            'formError' => $error,
            'errors' => $errors,
        ]);
    }
}
