<?php

declare(strict_types=1);

require_once __DIR__ . '/AppController.php';

final class ProjectController extends AppController
{
    public function index(): void
    {
        $this->render('projects', [
            'pageTitle' => 'Projekty',
            'projects' => [
                [
                    'id' => 1,
                    'name' => 'Projekt pierwszy',
                    'description' => 'Opis pierwszego projektu.',
                ],
                [
                    'id' => 2,
                    'name' => 'Projekt drugi',
                    'description' => 'Opis drugiego projektu.',
                ],
            ],
        ]);
    }

    public function show(int $id): void
    {
        $this->render('project', [
            'pageTitle' => 'Szczegóły projektu',
            'projectId' => $id,
        ]);
    }
}