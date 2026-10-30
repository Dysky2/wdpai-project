<?php

declare(strict_types=1);

require_once __DIR__ . '/AppController.php';

final class AboutController extends AppController
{
    public function index(): void
    {
        $this->render('about', [
            'pageTitle' => 'O aplikacji',
        ]);
    }
}
