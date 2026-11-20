<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Incident;
use App\Model\IncidentStatus;
use App\Repository\IncidentRepositoryInterface;

final class InMemoryIncidentRepository implements IncidentRepositoryInterface, Restorable
{
    /** @var array<int, Incident> */
    public array $rows = [];
    private int $nextId = 100;

    public function add(Incident $incident): void
    {
        $this->rows[(int) $incident->getId()] = $incident;
    }

    public function findById(int $id): ?Incident
    {
        return $this->rows[$id] ?? null;
    }

    public function findByIdForUpdate(int $id): ?Incident
    {
        return $this->findById($id);
    }

    public function save(Incident $incident): Incident
    {
        $saved = $incident->withId($this->nextId++);
        $this->rows[(int) $saved->getId()] = $saved;

        return $saved;
    }

    public function updateStatus(int $id, IncidentStatus $status): void
    {
        $this->rows[$id] = $this->rows[$id]->withStatus($status);
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
