<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Evidence;

interface EvidenceRepositoryInterface
{
    /** @return list<Evidence> */
    public function findByIncidentId(int $incidentId): array;

    /** Looks evidence up by its stored relative path (e.g. /uploads/<hex>.png); null when unknown. */
    public function findByFilePath(string $filePath): ?Evidence;

    /** Inserts the metadata and returns it with the generated id. */
    public function save(Evidence $evidence): Evidence;
}
