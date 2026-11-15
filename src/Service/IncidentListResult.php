<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\GrandPrix;

/** One page of the incident log together with counters and the filters that were actually applied. */
final class IncidentListResult
{
    /**
     * @param list<array<string, mixed>>   $rows         IncidentRow arrays (see IncidentService::toRow)
     * @param array<string, int>           $counts       TOTAL, REPORTED, UNDER_INVESTIGATION, PENALTY_ISSUED, NO_FURTHER_ACTION, CLOSED
     * @param list<GrandPrix>              $grandsPrix   options for the Grand Prix filter
     */
    public function __construct(
        private readonly array $rows,
        private readonly int $total,
        private readonly int $page,
        private readonly int $perPage,
        private readonly array $counts,
        private readonly string $statusGroup,
        private readonly ?int $grandPrixId,
        private readonly string $search,
        private readonly ?GrandPrix $activeGrandPrix,
        private readonly array $grandsPrix,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function getRows(): array { return $this->rows; }
    public function getTotal(): int { return $this->total; }
    public function getPage(): int { return $this->page; }
    public function getPerPage(): int { return $this->perPage; }
    public function getPages(): int { return max(1, (int) ceil($this->total / $this->perPage)); }
    /** @return array<string, int> */
    public function getCounts(): array { return $this->counts; }
    public function getStatusGroup(): string { return $this->statusGroup; }
    public function getGrandPrixId(): ?int { return $this->grandPrixId; }
    public function getSearch(): string { return $this->search; }
    public function getActiveGrandPrix(): ?GrandPrix { return $this->activeGrandPrix; }
    /** @return list<GrandPrix> */
    public function getGrandsPrix(): array { return $this->grandsPrix; }
}
