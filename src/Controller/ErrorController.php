<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;
use App\Service\ShellService;
use App\View\ViewHelper;

/**
 * Renders HTML error pages (400/401/403/404/422/500) inside the shared layout.
 */
final class ErrorController extends AbstractController
{
    private const PAGES = [
        400 => [
            'title' => 'Nieprawidłowe żądanie',
            'description' => 'Adres lub dane żądania są niepoprawne. Sprawdź link i spróbuj ponownie.',
        ],
        401 => [
            'title' => 'Wymagane logowanie',
            'description' => 'Aby zobaczyć tę stronę, musisz się zalogować.',
        ],
        403 => [
            'title' => 'Brak dostępu',
            'description' => 'Twoja rola nie pozwala na wykonanie tej operacji.',
        ],
        404 => [
            'title' => 'Nie znaleziono strony',
            'description' => 'Wybrana strona nie istnieje albo została przeniesiona. Wróć do centrum dowodzenia i wybierz inną sekcję.',
        ],
        422 => [
            'title' => 'Nie można przetworzyć żądania',
            'description' => 'Dane są poprawne składniowo, ale nie można ich zaakceptować.',
        ],
        500 => [
            'title' => 'Błąd serwera',
            'description' => 'Coś poszło nie tak po naszej stronie. Spróbuj ponownie za chwilę.',
        ],
    ];

    public function __construct(ViewHelper $view, private readonly ShellService $shell)
    {
        parent::__construct($view);
    }

    public function show(int $status): Response
    {
        $page = self::PAGES[$status] ?? self::PAGES[500];

        // The error page must still render when the failure came from the session or database.
        try {
            $shell = $this->shell->build('');
        } catch (\Throwable) {
            $shell = [];
        }

        return $this->render('error', $shell + [
            'pageTitle' => $page['title'] . ' — ApexSteward',
            'pageClass' => 'page--centered',
            'status' => $status,
            'title' => $page['title'],
            'description' => $page['description'],
        ])->setStatusCode($status);
    }
}
