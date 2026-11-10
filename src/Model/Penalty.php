<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents a sanction (penalty) issued by a steward.
 * Superlicence points expire 365 days after issuance.
 */
final class Penalty
{
    public function __construct(
        private ?int    $id,
        private int     $incidentId,
        private int     $driverId,
        private int     $stewardId,
        private string  $articleReference,  // e.g. 'Art. 38.3 Sporting Regulations'
        private string  $sanctionType,      // 'TIME_PENALTY' | 'GRID_PENALTY' | 'SUPERLICENCE_POINTS' | 'DISQUALIFICATION'
        private float   $sanctionValue,
        private int     $superlicencePoints,
        private bool    $isExpired,
        private \DateTimeImmutable $issuedAt,
    ) {}

    public function getId(): ?int                  { return $this->id; }
    public function getIncidentId(): int           { return $this->incidentId; }
    public function getDriverId(): int             { return $this->driverId; }
    public function getStewardId(): int            { return $this->stewardId; }
    public function getArticleReference(): string  { return $this->articleReference; }
    public function getSanctionType(): string      { return $this->sanctionType; }
    public function getSanctionValue(): float      { return $this->sanctionValue; }
    public function getSuperlicencePoints(): int   { return $this->superlicencePoints; }
    public function isExpired(): bool              { return $this->isExpired; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function markExpired(): self
    {
        $clone = clone $this;
        $clone->isExpired = true;
        return $clone;
    }
}
