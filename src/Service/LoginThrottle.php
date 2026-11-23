<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\ClockInterface;
use App\Repository\LoginAttemptRepositoryInterface;

/**
 * Brute-force protection: after MAX_ATTEMPTS failed logins for one e-mail within WINDOW_MINUTES
 * further attempts are refused until the oldest of those failures leaves the window.
 * The key is the e-mail whether or not the account exists, so the lock reveals nothing about accounts.
 */
final class LoginThrottle
{
    public const MAX_ATTEMPTS = 5;
    public const WINDOW_MINUTES = 10;

    public function __construct(
        private readonly LoginAttemptRepositoryInterface $attempts,
        private readonly ClockInterface $clock,
    ) {
    }

    public function isLocked(string $email): bool
    {
        return $this->attempts->countSince($this->key($email), $this->windowStart()) >= self::MAX_ATTEMPTS;
    }

    /** Minutes (at least 1) until a locked e-mail may try again; 0 when not locked. */
    public function minutesUntilUnlock(string $email): int
    {
        $key = $this->key($email);
        $since = $this->windowStart();
        if ($this->attempts->countSince($key, $since) < self::MAX_ATTEMPTS) {
            return 0;
        }

        $oldest = $this->attempts->oldestSince($key, $since);
        if ($oldest === null) {
            return 0;
        }

        $unlockAt = $oldest->modify(sprintf('+%d minutes', self::WINDOW_MINUTES));
        $seconds = max(0, $unlockAt->getTimestamp() - $this->clock->now()->getTimestamp());

        return max(1, (int) ceil($seconds / 60));
    }

    public function recordFailure(string $email, string $ip): void
    {
        $this->attempts->record($this->key($email), $ip, $this->clock->now());
    }

    public function reset(string $email): void
    {
        $this->attempts->clear($this->key($email));
    }

    private function windowStart(): \DateTimeImmutable
    {
        return $this->clock->now()->modify(sprintf('-%d minutes', self::WINDOW_MINUTES));
    }

    private function key(string $email): string
    {
        return mb_substr(mb_strtolower(trim($email)), 0, 254);
    }
}
