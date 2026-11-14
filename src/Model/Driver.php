<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Formula 1 driver; the three-letter code (VER, HAM) is unique.
 */
final class Driver
{
    public function __construct(
        private readonly int $id,
        private readonly int $teamId,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $code,
        private readonly int $raceNumber,
        private readonly bool $isSuspended,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getTeamId(): int { return $this->teamId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getCode(): string { return $this->code; }
    public function getRaceNumber(): int { return $this->raceNumber; }
    public function isSuspended(): bool { return $this->isSuspended; }
}
