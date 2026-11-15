<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Read model of the case file (incident + Grand Prix + drivers + teams + article + reporter + evidence + penalty).
 */
final class IncidentDetail
{
    /**
     * @param array{number: int, code: string, name: string, team: string, teamId: int} $accused
     * @param ?array{number: int, code: string, name: string, team: string, teamId: int} $affected
     * @param ?array{type: PenaltyType, value: ?int, points: int, reason: string, articleTitle: string, articleReference: string} $penalty
     */
    public function __construct(
        private readonly int $id,
        private readonly string $grandPrixName,
        private readonly string $circuitName,
        private readonly int $totalLaps,
        private readonly int $lapNumber,
        private readonly array $accused,
        private readonly ?array $affected,
        private readonly string $articleTitle,
        private readonly string $articleReference,
        private readonly string $description,
        private readonly IncidentStatus $status,
        private readonly \DateTimeImmutable $reportedAt,
        private readonly string $reporterName,
        private readonly string $reporterTeam,
        private readonly int $reporterTeamId,
        private readonly ?Evidence $evidence,
        private readonly ?array $penalty,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getCode(): string { return sprintf('INC-%03d', $this->id); }
    public function getGrandPrixName(): string { return $this->grandPrixName; }
    public function getCircuitName(): string { return $this->circuitName; }
    public function getTotalLaps(): int { return $this->totalLaps; }
    public function getLapNumber(): int { return $this->lapNumber; }
    /** @return array{number: int, code: string, name: string, team: string, teamId: int} */
    public function getAccused(): array { return $this->accused; }
    /** @return ?array{number: int, code: string, name: string, team: string, teamId: int} */
    public function getAffected(): ?array { return $this->affected; }
    public function getArticleTitle(): string { return $this->articleTitle; }
    public function getArticleReference(): string { return $this->articleReference; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): IncidentStatus { return $this->status; }
    public function getReportedAt(): \DateTimeImmutable { return $this->reportedAt; }
    public function getReporterName(): string { return $this->reporterName; }
    public function getReporterTeam(): string { return $this->reporterTeam; }
    public function getReporterTeamId(): int { return $this->reporterTeamId; }
    public function getEvidence(): ?Evidence { return $this->evidence; }
    /** @return ?array{type: PenaltyType, value: ?int, points: int, reason: string, articleTitle: string, articleReference: string} */
    public function getPenalty(): ?array { return $this->penalty; }

    /** A team principal may open the case when their team reported it or is accused in it. */
    public function belongsToTeam(int $teamId): bool
    {
        return $this->reporterTeamId === $teamId || $this->accused['teamId'] === $teamId;
    }
}
