<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Grand Prix weekend; totalLaps bounds the lap number of a protest.
 */
final class GrandPrix
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $circuitName,
        private readonly string $country,
        private readonly int $seasonYear,
        private readonly int $roundNumber,
        private readonly \DateTimeImmutable $raceDate,
        private readonly int $totalLaps,
        private readonly bool $isActive,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCircuitName(): string { return $this->circuitName; }
    public function getCountry(): string { return $this->country; }
    public function getSeasonYear(): int { return $this->seasonYear; }
    public function getRoundNumber(): int { return $this->roundNumber; }
    public function getRaceDate(): \DateTimeImmutable { return $this->raceDate; }
    public function getTotalLaps(): int { return $this->totalLaps; }
    public function isActive(): bool { return $this->isActive; }
}
