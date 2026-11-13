<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Driver;

/**
 * Contract for driver data persistence operations.
 */
interface DriverRepositoryInterface
{
    /**
     * Find a driver by their primary key.
     */
    public function findById(int $id): ?Driver;

    /**
     * Return all drivers, ordered by driver number.
     *
     * @return Driver[]
     */
    public function findAll(): array;

    /**
     * Return all drivers belonging to a specific team.
     *
     * @return Driver[]
     */
    public function findByTeamId(int $teamId): array;

    /**
     * Update the suspension flag for a driver.
     */
    public function updateSuspension(int $id, bool $isSuspended): void;
}
