<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Driver;

interface DriverRepositoryInterface
{
    public function findById(int $id): ?Driver;

    /** @return list<Driver> ordered by race number */
    public function findAll(): array;

    /** @return list<Driver> */
    public function findByTeamId(int $teamId): array;

    /** SELECT ... FOR UPDATE: serialises concurrent rulings for the same driver (call inside a transaction). */
    public function lockById(int $id): ?Driver;

    public function updateSuspension(int $id, bool $isSuspended): void;
}
