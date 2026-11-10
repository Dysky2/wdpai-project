<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Incident;

/**
 * Contract for incident data persistence operations.
 */
interface IncidentRepositoryInterface
{
    /**
     * Find an incident by its primary key.
     */
    public function findById(int $id): ?Incident;

    /**
     * Return all incidents, optionally filtered by status.
     *
     * @return Incident[]
     */
    public function findAll(?string $status = null): array;

    /**
     * Return incidents filed by or against a specific team.
     *
     * @return Incident[]
     */
    public function findByTeamId(int $teamId): array;

    /**
     * Persist a new incident and return the entity with the generated id.
     */
    public function save(Incident $incident): Incident;

    /**
     * Update the status of an existing incident.
     */
    public function updateStatus(int $id, string $status): void;
}
