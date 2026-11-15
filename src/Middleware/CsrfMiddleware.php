<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\ForbiddenException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Session;

/** Requires a valid CSRF token (csrf_token field or X-CSRF-Token header) on every state-changing request. */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $request->getCsrfToken();
        if (!is_string($token) || $token === '' || !hash_equals($this->session->csrfToken(), $token)) {
            throw new ForbiddenException('Invalid CSRF token');
        }

        return $next($request);
    }
}
