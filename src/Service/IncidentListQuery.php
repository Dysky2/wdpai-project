<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UnprocessableEntityException;
use App\Model\IncidentCriteria;

/**
 * Validated query of the incident log, shared by GET /incidents (HTML) and GET /api/incidents (JSON),
 * so both accept the same values and answer an invalid one in the same way (HTTP 422).
 *
 * - status: one of IncidentCriteria::GROUPS (absent = "all")
 * - gp:     "all" or a numeric Grand Prix id (absent = active Grand Prix; an unknown id also falls back to it)
 * - q:      free text (non-string values are ignored)
 * - page:   integer >= 1 (absent = 1; a page past the last one is clamped by the service)
 */
final class IncidentListQuery
{
    private function __construct(
        private readonly string $statusGroup,
        private readonly ?string $grandPrix,
        private readonly string $search,
        private readonly int $page,
    ) {
    }

    /**
     * @param array<string, mixed> $query raw query-string values
     * @throws UnprocessableEntityException naming the first invalid parameter
     */
    public static function fromArray(array $query): self
    {
        $status = $query['status'] ?? null;
        $gp = $query['gp'] ?? null;
        $page = $query['page'] ?? null;
        $search = $query['q'] ?? '';

        $invalid = match (true) {
            $status !== null && !(is_string($status) && IncidentCriteria::isGroup($status)) => 'status',
            $gp !== null && !(is_string($gp) && ($gp === IncidentService::ALL_GRANDS_PRIX || ctype_digit($gp))) => 'gp',
            $page !== null && !(is_string($page) && ctype_digit($page) && (int) $page >= 1) => 'page',
            default => null,
        };

        if ($invalid !== null) {
            throw new UnprocessableEntityException(sprintf('Invalid value of query parameter "%s".', $invalid));
        }

        return new self(
            is_string($status) ? $status : IncidentCriteria::GROUP_ALL,
            is_string($gp) ? $gp : null,
            is_string($search) ? $search : '',
            is_string($page) ? (int) $page : 1,
        );
    }

    public function getStatusGroup(): string { return $this->statusGroup; }
    /** "all", a Grand Prix id, or null for the active Grand Prix. */
    public function getGrandPrix(): ?string { return $this->grandPrix; }
    public function getSearch(): string { return $this->search; }
    public function getPage(): int { return $this->page; }
}
