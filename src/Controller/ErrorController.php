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
    private const STATUSES = [400, 401, 403, 404, 422, 500];

    public function __construct(ViewHelper $view, private readonly ShellService $shell)
    {
        parent::__construct($view);
    }

    public function show(int $status): Response
    {
        $status = in_array($status, self::STATUSES, true) ? $status : 500;
        $title = $this->view->t('error.' . $status . '.title');

        // The error page must still render when the failure came from the session or database.
        try {
            $shell = $this->shell->build('');
        } catch (\Throwable) {
            $shell = [];
        }

        return $this->render('error', $shell + [
            'pageTitle' => $title . ' — ApexSteward',
            'pageClass' => 'page--centered',
            'status' => $status,
            'title' => $title,
            'description' => $this->view->t('error.' . $status . '.description'),
        ])->setStatusCode($status);
    }
}
