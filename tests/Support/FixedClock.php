<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\ClockInterface;

final class FixedClock implements ClockInterface
{
    public function __construct(private \DateTimeImmutable $now = new \DateTimeImmutable('2026-06-01 12:00:00 UTC'))
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function advanceMinutes(int $minutes): void
    {
        $this->now = $this->now->modify(sprintf('+%d minutes', $minutes));
    }

    public function advanceDays(int $days): void
    {
        $this->now = $this->now->modify(sprintf('+%d days', $days));
    }
}
