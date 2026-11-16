<?php

declare(strict_types=1);

namespace App\Service;

/** Superlicence rules in one place: points live 365 days, 12 active points mean a one-race suspension. */
final class PenaltyPolicy
{
    public const EXPIRY_DAYS = 365;
    public const SUSPENSION_THRESHOLD = 12;
    public const MAX_POINTS_PER_PENALTY = 3;

    public function expiryFor(\DateTimeImmutable $issuedAt): \DateTimeImmutable
    {
        return $issuedAt->modify('+' . self::EXPIRY_DAYS . ' days');
    }

    public function shouldSuspend(int $activePoints): bool
    {
        return $activePoints >= self::SUSPENSION_THRESHOLD;
    }
}
