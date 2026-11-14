<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Source of the current time; replaced by a fixed clock in tests.
 */
interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
