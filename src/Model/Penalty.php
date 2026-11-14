<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Sanction issued by a steward for an incident. Points stay active until expiresAt (issue date + 365 days).
 */
final class Penalty
{
    public function __construct(
        private ?int $id,
        private readonly int $incidentId,
        private readonly int $driverId,
        private readonly int $stewardId,
        private readonly int $articleId,
        private readonly PenaltyType $type,
        private readonly ?int $sanctionValue,
        private readonly int $pointsAdded,
        private readonly string $reason,
        private readonly \DateTimeImmutable $issuedAt,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly bool $isExpired,
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getIncidentId(): int { return $this->incidentId; }
    public function getDriverId(): int { return $this->driverId; }
    public function getStewardId(): int { return $this->stewardId; }
    public function getArticleId(): int { return $this->articleId; }
    public function getType(): PenaltyType { return $this->type; }
    public function getSanctionValue(): ?int { return $this->sanctionValue; }
    public function getPointsAdded(): int { return $this->pointsAdded; }
    public function getReason(): string { return $this->reason; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }
    public function isExpired(): bool { return $this->isExpired; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }
}
