<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Penalty;

/**
 * Contract for penalty (sanction) data persistence operations.
 */
interface PenaltyRepositoryInterface
{
    /**
     * Find a penalty by its primary key.
     */
    public function findById(int $id): ?Penalty;

    /**
     * Return all penalties for a specific incident.
     *
     * @return Penalty[]
     */
    public function findByIncidentId(int $incidentId): array;

    /**
     * Return all active (non-expired) penalties for a driver.
     *
     * @return Penalty[]
     */
    public function findActiveByDriverId(int $driverId): array;

    /**
     * Return all penalties older than 365 days that are not yet marked as expired.
     *
     * @return Penalty[]
     */
    public function findExpiredCandidates(): array;

    /**
     * Persist a new penalty and return the entity with the generated id.
     */
    public function save(Penalty $penalty): Penalty;

    /**
     * Mark a penalty as expired (is_expired = true).
     */
    public function markExpired(int $id): void;
}
