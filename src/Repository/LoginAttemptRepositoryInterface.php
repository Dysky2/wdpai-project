<?php

declare(strict_types=1);

namespace App\Repository;

interface LoginAttemptRepositoryInterface
{
    /** Stores one failed attempt. */
    public function record(string $emailKey, string $ip, \DateTimeImmutable $at): void;

    /** Failed attempts for the key at or after $since. */
    public function countSince(string $emailKey, \DateTimeImmutable $since): int;

    /** Time of the oldest failed attempt at or after $since, or null when there is none. */
    public function oldestSince(string $emailKey, \DateTimeImmutable $since): ?\DateTimeImmutable;

    /** Forgets all failed attempts for the key (after a successful login). */
    public function clear(string $emailKey): void;
}
