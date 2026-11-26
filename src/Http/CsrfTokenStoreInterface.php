<?php

declare(strict_types=1);

namespace App\Http;

/** Source of the per-session CSRF token (the session in production, a fake in tests). */
interface CsrfTokenStoreInterface
{
    public function csrfToken(): string;
}
