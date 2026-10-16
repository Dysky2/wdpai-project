<?php

declare(strict_types=1);

require_once __DIR__ . '/AppController.php';

final class SecurityController extends AppController
{
    public function login(): void
    {
        $this->render('login', [
            'pageTitle' => 'Ekran logowania',
        ]);
    }

    public function register(): void
    {
        $this->render('register', [
            'pageTitle' => 'Ekran rejestracji',
        ]);
    }

}