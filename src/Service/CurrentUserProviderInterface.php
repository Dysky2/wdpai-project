<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;

/** Gives the logged-in user (or null for a guest); used by the authentication and role middleware. */
interface CurrentUserProviderInterface
{
    public function currentUser(): ?User;
}
