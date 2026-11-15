<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Filters for the incident log: ownership scope, Grand Prix, status group and free-text search.
 */
final class IncidentCriteria
{
    public const GROUP_ALL = 'all';
    public const GROUP_REPORTED = 'reported';
    public const GROUP_INVESTIGATION = 'investigation';
    public const GROUP_CLOSED = 'closed';

    public const GROUPS = [self::GROUP_ALL, self::GROUP_REPORTED, self::GROUP_INVESTIGATION, self::GROUP_CLOSED];

    /**
     * @param ?int   $teamScope   null = every case (steward); otherwise only cases where that team reported or is accused
     * @param ?int   $grandPrixId null = every Grand Prix
     * @param string $statusGroup one of self::GROUPS
     */
    public function __construct(
        private readonly ?int $teamScope,
        private readonly ?int $grandPrixId,
        private readonly string $statusGroup = self::GROUP_ALL,
        private readonly string $search = '',
    ) {
    }

    public function getTeamScope(): ?int { return $this->teamScope; }
    public function getGrandPrixId(): ?int { return $this->grandPrixId; }
    public function getStatusGroup(): string { return $this->statusGroup; }
    public function getSearch(): string { return $this->search; }

    /** @return list<IncidentStatus> statuses matched by the status group (empty = no status filter) */
    public function getStatuses(): array
    {
        return match ($this->statusGroup) {
            self::GROUP_REPORTED => [IncidentStatus::REPORTED],
            self::GROUP_INVESTIGATION => [IncidentStatus::UNDER_INVESTIGATION],
            self::GROUP_CLOSED => [IncidentStatus::PENALTY_ISSUED, IncidentStatus::NO_FURTHER_ACTION],
            default => [],
        };
    }
}
