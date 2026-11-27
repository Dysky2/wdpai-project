<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Service\LoginAuditLogger;
use App\Service\LoginThrottle;
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
        private readonly LoginAuditLogger $audit,
        private readonly LoginThrottle $throttle,
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
            $this->audit->logFailure($email, $request->getClientIp(), 'invalid_input');
            $errors = array_map(fn (string $key): string => $this->view->t($key), $errorKeys);

            return $this->renderLogin($email, null, $errors)->setStatusCode(422);
        }

        if ($this->throttle->isLocked($email)) {
            $this->audit->logFailure($email, $request->getClientIp(), 'locked_out');
            $message = $this->view->t('login.locked', ['minutes' => $this->throttle->minutesUntilUnlock($email)]);

            return $this->renderLogin($email, $message)
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) ($this->throttle->minutesUntilUnlock($email) * 60));
        }

        $user = $this->auth->attempt($email, $password);
        if ($user === null) {
            $this->throttle->recordFailure($email, $request->getClientIp());
            $this->audit->logFailure($email, $request->getClientIp(), 'invalid_credentials');
            return $this->renderLogin($email, $this->view->t('login.invalid_credentials'))->setStatusCode(422);
        }

        $this->throttle->reset($email);
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
            'formError' => $error,
            // Settings for partials/form-field.html.php; length limits come from the validator.
            'emailField' => [
                'id' => 'email',
                'name' => 'email',
                'label' => $this->view->t('login.email'),
                'type' => 'email',
                'autocomplete' => 'username',
                'maxlength' => LoginValidator::EMAIL_MAX_LENGTH,
                'placeholder' => $this->view->t('login.email_placeholder'),
                'value' => $email,
                'error' => $errors['email'] ?? null,
            ],
            'passwordField' => [
                'id' => 'password',
                'name' => 'password',
                'label' => $this->view->t('login.password'),
                'type' => 'password',
                'autocomplete' => 'current-password',
                'maxlength' => LoginValidator::PASSWORD_MAX_LENGTH,
                'placeholder' => $this->view->t('login.password_placeholder'),
                'error' => $errors['password'] ?? null,
            ],
        ]);
    }
}
