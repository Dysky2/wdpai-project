<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Racing incident (protest) filed by a team principal. Evidence is stored separately (see Evidence).
 */
final class Incident
{
    public function __construct(
        private ?int $id,
        private readonly int $grandPrixId,
        private readonly int $reportingUserId,
        private readonly int $accusedDriverId,
        private readonly ?int $affectedDriverId,
        private readonly int $infringementArticleId,
        private readonly int $lapNumber,
        private readonly string $description,
        private IncidentStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getGrandPrixId(): int { return $this->grandPrixId; }
    public function getReportingUserId(): int { return $this->reportingUserId; }
    public function getAccusedDriverId(): int { return $this->accusedDriverId; }
    public function getAffectedDriverId(): ?int { return $this->affectedDriverId; }
    public function getInfringementArticleId(): int { return $this->infringementArticleId; }
    public function getLapNumber(): int { return $this->lapNumber; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): IncidentStatus { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** Display code, e.g. INC-042. */
    public function getCode(): string
    {
        return self::codeFor($this->id ?? 0);
    }

    /** The single place that formats a case id as its display code ("INC-" + id padded to 3 digits). */
    public static function codeFor(int $id): string
    {
        return sprintf('INC-%03d', $id);
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    /** @throws \App\Exception\UnprocessableEntityException when the transition is not allowed */
    public function withStatus(IncidentStatus $status): self
    {
        $this->status->assertCanTransitionTo($status);
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }
}
