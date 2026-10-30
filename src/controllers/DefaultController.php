<?php

declare(strict_types=1);

require_once __DIR__ . '/AppController.php';


final class DefaultController extends AppController
{
    public function index(): void
    {
        $this->render('home', [
            'pageTitle' => 'Panel sędziów F1',
            'activeRoute' => '/',
            'race' => [
                'name' => 'Grand Prix São Paulo',
                'round' => 'Runda 21 / 24',
                'date' => '03 LISTOPADA 2026',
                'status' => 'W toku',
            ],
            'stats' => [
                ['label' => 'Otwarte sprawy', 'value' => '04', 'trend' => '+2 dziś'],
                ['label' => 'Rozstrzygnięte', 'value' => '18', 'trend' => 'Ten weekend'],
                ['label' => 'Czas reakcji', 'value' => '08 min', 'trend' => '-12% vs. poprzednio'],
            ],
            'judges' => [
                ['name' => 'Marta Kowalska', 'role' => 'Przewodnicząca panelu', 'status' => 'Aktywna', 'initials' => 'MK'],
                ['name' => 'James Wilson', 'role' => 'Sędzia FIA', 'status' => 'Aktywny', 'initials' => 'JW'],
                ['name' => 'Rafael Silva', 'role' => 'Doradca kierowców', 'status' => 'Aktywny', 'initials' => 'RS'],
            ],
        ]);
    }

    public function notFound(): void
    {
        $this->render('not-found', [
            'pageTitle' => 'Nie znaleziono strony',
        ]);
    }
}