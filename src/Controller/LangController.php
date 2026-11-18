<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\View\ViewHelper;

/** GET /lang/(pl|en): stores the language in the session and returns to the previous page. */
final class LangController extends AbstractController
{
    public function __construct(ViewHelper $view, private readonly Session $session)
    {
        parent::__construct($view);
    }

    public function switch(Request $request, string $lang): Response
    {
        $this->session->setLang($lang);

        return Response::redirect($this->backTarget($request));
    }

    /** Only a path on this site is accepted from Referer (prevents an open redirect). */
    private function backTarget(Request $request): string
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

        return str_starts_with($path, '/') && !str_starts_with($path, '//')
            ? $path . (isset($parts['query']) ? '?' . $parts['query'] : '')
            : '/';
    }
}
