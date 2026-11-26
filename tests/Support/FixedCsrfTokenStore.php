<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Http\CsrfTokenStoreInterface;

/** Session CSRF token with a known value, so tests can send a valid or a wrong one. */
final class FixedCsrfTokenStore implements CsrfTokenStoreInterface
{
    public const TOKEN = 'a3f1c0de5eed5eed5eed5eed5eed5eed5eed5eed5eed5eed5eed5eed5eed5eed';

    public function csrfToken(): string
    {
        return self::TOKEN;
    }
}
