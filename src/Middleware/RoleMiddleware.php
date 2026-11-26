<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\ForbiddenException;
use App\Exception\UnauthorizedException;
use App\Http\Request;
use App\Http\Response;
use App\Model\Role;
use App\Service\CurrentUserProviderInterface;

/** Allows only the listed roles; everyone else gets 403 (use after AuthMiddleware). */
final class RoleMiddleware implements MiddlewareInterface
{
    /** @var list<Role> */
    private readonly array $roles;

    public function __construct(private readonly CurrentUserProviderInterface $auth, Role ...$roles)
    {
        $this->roles = array_values($roles);
    }

    public static function for(CurrentUserProviderInterface $auth, Role ...$roles): self
    {
        return new self($auth, ...$roles);
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();

        if (!in_array($user->getRole(), $this->roles, true)) {
            throw new ForbiddenException('Role not allowed');
        }

        return $next($request);
    }
}
