<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\CsrfTokenException;
use App\Http\CsrfTokenStoreInterface;
use App\Http\Request;
use App\Http\Response;

/**
 * Requires a valid CSRF token (csrf_token field or X-CSRF-Token header) on every state-changing request.
 * On protected routes it runs after AuthMiddleware and before RoleMiddleware (401, then 403 csrf_invalid, then 403 forbidden).
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly CsrfTokenStoreInterface $tokens)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $request->getCsrfToken();
        if (!is_string($token) || $token === '' || !hash_equals($this->tokens->csrfToken(), $token)) {
            throw new CsrfTokenException();
        }

        return $next($request);
    }
}
