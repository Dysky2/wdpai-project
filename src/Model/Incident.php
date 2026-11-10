<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents a racing incident / protest filed by a team.
 */
final class Incident
{
    public function __construct(
        private ?int    $id,
        private int     $grandPrixId,
        private int     $lap,
        private int     $reportedByTeamId,
        private int     $accusedDriverId,
        private string  $description,
        private string  $status,         // 'REPORTED' | 'UNDER_INVESTIGATION' | 'PENALTY_ISSUED' | 'NO_FURTHER_ACTION'
        private ?string $evidenceFilePath,
        private \DateTimeImmutable $reportedAt,
    ) {}

    public function getId(): ?int               { return $this->id; }
    public function getGrandPrixId(): int        { return $this->grandPrixId; }
    public function getLap(): int                { return $this->lap; }
    public function getReportedByTeamId(): int   { return $this->reportedByTeamId; }
    public function getAccusedDriverId(): int     { return $this->accusedDriverId; }
    public function getDescription(): string     { return $this->description; }
    public function getStatus(): string          { return $this->status; }
    public function getEvidenceFilePath(): ?string { return $this->evidenceFilePath; }
    public function getReportedAt(): \DateTimeImmutable { return $this->reportedAt; }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withStatus(string $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function withEvidenceFilePath(string $path): self
    {
        $clone = clone $this;
        $clone->evidenceFilePath = $path;
        return $clone;
    }
}
