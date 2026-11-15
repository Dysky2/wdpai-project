<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Read model for one row of the incident log (incident + drivers + article + optional penalty).
 */
final class IncidentSummary
{
    /**
     * @param list<array{number: int, code: string, name: string}> $drivers accused driver first, then the affected one
     */
    public function __construct(
        private readonly int $id,
        private readonly int $lapNumber,
        private readonly array $drivers,
        private readonly string $articleTitle,
        private readonly string $description,
        private readonly IncidentStatus $status,
        private readonly ?PenaltyType $penaltyType,
        private readonly ?int $penaltyValue,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getCode(): string { return sprintf('INC-%03d', $this->id); }
    public function getLapNumber(): int { return $this->lapNumber; }
    /** @return list<array{number: int, code: string, name: string}> */
    public function getDrivers(): array { return $this->drivers; }
    public function getArticleTitle(): string { return $this->articleTitle; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): IncidentStatus { return $this->status; }
    public function getPenaltyType(): ?PenaltyType { return $this->penaltyType; }
    public function getPenaltyValue(): ?int { return $this->penaltyValue; }
}
