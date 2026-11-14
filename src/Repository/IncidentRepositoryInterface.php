<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Incident;
use App\Model\IncidentStatus;

interface IncidentRepositoryInterface
{
    public function findById(int $id): ?Incident;

    /** SELECT ... FOR UPDATE (call inside a transaction before changing the status). */
    public function findByIdForUpdate(int $id): ?Incident;

    /** Inserts the incident and returns it with the generated id. */
    public function save(Incident $incident): Incident;

    public function updateStatus(int $id, IncidentStatus $status): void;
}
