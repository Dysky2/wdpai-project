<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\IncidentCriteria;
use App\Model\IncidentSummary;

/** Read side of the incident log (list, filters, counters). */
interface IncidentListRepositoryInterface
{
    /** @return list<IncidentSummary> newest first */
    public function findPage(IncidentCriteria $criteria, int $limit, int $offset): array;

    /** Number of rows matching every filter in the criteria. */
    public function count(IncidentCriteria $criteria): int;

    /**
     * Cases per status within the team scope and Grand Prix of the criteria
     * (status group and search are ignored, so the counters do not change while filtering).
     *
     * @return array<string, int> keyed by IncidentStatus value; every status is present
     */
    public function countByStatus(IncidentCriteria $criteria): array;
}
