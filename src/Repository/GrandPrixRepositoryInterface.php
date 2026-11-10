<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\GrandPrix;

/**
 * Contract for Grand Prix event data persistence operations.
 */
interface GrandPrixRepositoryInterface
{
    /**
     * Find a Grand Prix by its primary key.
     */
    public function findById(int $id): ?GrandPrix;

    /**
     * Return all Grand Prix events for a given season, ordered by round.
     *
     * @return GrandPrix[]
     */
    public function findBySeason(int $season): array;

    /**
     * Return the currently active (most recent / in-progress) Grand Prix.
     */
    public function findCurrent(): ?GrandPrix;
}
