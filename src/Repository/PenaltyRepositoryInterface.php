<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Penalty;

interface PenaltyRepositoryInterface
{
    public function findById(int $id): ?Penalty;

    /** @return list<Penalty> */
    public function findByIncidentId(int $incidentId): array;

    /** Penalties that still count: NOT is_expired AND expires_at > now. @return list<Penalty> */
    public function findActiveByDriverId(int $driverId): array;

    /** Sum of points of the active penalties of a driver (same rule as the standings view). */
    public function sumActivePoints(int $driverId): int;

    /** Inserts the penalty and returns it with the generated id. */
    public function save(Penalty $penalty): Penalty;

    /**
     * Flags every not yet expired penalty whose expires_at <= $now (UPDATE ... RETURNING), used by the CRON job.
     *
     * @return list<array{driverId: int, points: int}> one entry per expired penalty
     */
    public function expireOutdated(\DateTimeImmutable $now): array;
}
