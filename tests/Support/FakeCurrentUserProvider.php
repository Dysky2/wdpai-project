<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\User;
use App\Service\CurrentUserProviderInterface;

/** Logged-in user for middleware tests (null = guest). */
final class FakeCurrentUserProvider implements CurrentUserProviderInterface
{
    public function __construct(private ?User $user = null)
    {
    }

    public function logIn(?User $user): void
    {
        $this->user = $user;
    }

    public function currentUser(): ?User
    {
        return $this->user;
    }
}
