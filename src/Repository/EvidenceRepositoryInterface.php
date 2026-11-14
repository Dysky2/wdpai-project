<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Evidence;

interface EvidenceRepositoryInterface
{
    /** @return list<Evidence> */
    public function findByIncidentId(int $incidentId): array;

    /** Inserts the metadata and returns it with the generated id. */
    public function save(Evidence $evidence): Evidence;
}
