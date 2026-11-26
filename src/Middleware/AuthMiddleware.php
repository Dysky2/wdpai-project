<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\UnauthorizedException;
use App\Http\Request;
use App\Http\Response;
use App\Service\CurrentUserProviderInterface;

/** Lets only logged-in users through: HTML gets a redirect to /login, /api/* gets 401 JSON. */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly CurrentUserProviderInterface $auth)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->auth->currentUser() === null) {
            if (str_starts_with($request->getPath(), '/api/')) {
                throw new UnauthorizedException();
            }

            return Response::redirect('/login');
        }

        return $next($request)->setHeader('Cache-Control', 'no-store, private');
    }
}
