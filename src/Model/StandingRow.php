<?php

declare(strict_types=1);

namespace App\Model;

/**
 * One row of the Super Licence table (view v_super_licence_standings); the status is derived in PHP.
 */
final class StandingRow
{
    public function __construct(
        private readonly int $driverId,
        private readonly string $code,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly int $raceNumber,
        private readonly int $teamId,
        private readonly string $teamName,
        private readonly bool $isSuspended,
        private readonly int $activePoints,
        private readonly ?\DateTimeImmutable $nextExpiration,
    ) {
    }

    public function getDriverId(): int { return $this->driverId; }
    public function getCode(): string { return $this->code; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getRaceNumber(): int { return $this->raceNumber; }
    public function getTeamId(): int { return $this->teamId; }
    public function getTeamName(): string { return $this->teamName; }
    public function isSuspended(): bool { return $this->isSuspended; }
    public function getActivePoints(): int { return $this->activePoints; }
    public function getNextExpiration(): ?\DateTimeImmutable { return $this->nextExpiration; }

    public function getLicenceStatus(): LicenceStatus
    {
        return LicenceStatus::fromPoints($this->activePoints);
    }

    /** Progress bar fill, 0-100 (points / 12, capped). */
    public function getProgressPercent(): int
    {
        return min(100, (int) round($this->activePoints / LicenceStatus::POINTS_LIMIT * 100));
    }
}
