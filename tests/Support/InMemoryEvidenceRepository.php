<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Evidence;
use App\Repository\EvidenceRepositoryInterface;

final class InMemoryEvidenceRepository implements EvidenceRepositoryInterface, Restorable
{
    /** @var list<Evidence> */
    public array $rows = [];

    /** When set, save() throws it, to simulate a database failure after the file was stored. */
    public ?\Throwable $failOnSave = null;

    public function findByIncidentId(int $incidentId): array
    {
        return array_values(array_filter($this->rows, static fn (Evidence $e): bool => $e->getIncidentId() === $incidentId));
    }

    public function save(Evidence $evidence): Evidence
    {
        if ($this->failOnSave !== null) {
            throw $this->failOnSave;
        }
        $saved = $evidence->withId(count($this->rows) + 1);
        $this->rows[] = $saved;

        return $saved;
    }

    public function snapshot(): mixed
    {
        return $this->rows;
    }

    public function restore(mixed $snapshot): void
    {
        $this->rows = $snapshot;
    }
}
