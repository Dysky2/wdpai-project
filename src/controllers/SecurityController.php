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

    public function profil(int $id): void 
    {
        $name = 'jan_kowalski';
        $email = 'jan.kowalski@example.com';
        $role = 'Użytkownik';

        $this->render('profil', [
            'pageTitle' => 'Szczegóły profilu usera',
            'profilId' => $id,
            'profilName' => $name,
            'profilEmail' => $email,
            'profilRole' => $role,
        ]);
    }
}