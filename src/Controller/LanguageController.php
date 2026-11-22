<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\View\ViewHelper;

/** Stores the chosen interface language in the session and returns to the previous page. */
final class LanguageController extends AbstractController
{
    public function __construct(ViewHelper $view, private readonly Session $session)
    {
        parent::__construct($view);
    }

    public function switch(Request $request, string $lang): Response
    {
        $this->session->setLang($lang);

        return Response::redirect($this->safeReturnPath($request));
    }

    /** Only a same-host Referer is honoured, and only its path + query (no open redirect). */
    private function safeReturnPath(Request $request): string
    {
        $referer = $request->getHeader('Referer');
        if ($referer === null || $referer === '') {
            return '/';
        }

        $parts = parse_url($referer);
        $host = $request->getHeader('Host');
        if ($parts === false || ($parts['host'] ?? null) === null || $host === null) {
            return '/';
        }
        $refererHost = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (strcasecmp($refererHost, $host) !== 0) {
            return '/';
        }

        $path = $parts['path'] ?? '/';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
