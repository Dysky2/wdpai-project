<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Driver;
use App\Repository\DriverRepositoryInterface;

final class InMemoryDriverRepository implements DriverRepositoryInterface, Restorable
{
    /** @var array<int, Driver> */
    private array $rows = [];

    public function __construct(Driver ...$drivers)
    {
        foreach ($drivers as $driver) {
            $this->rows[$driver->getId()] = $driver;
        }
    }

    public function findById(int $id): ?Driver
    {
        return $this->rows[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->rows);
    }

    public function findByTeamId(int $teamId): array
    {
        return array_values(array_filter($this->rows, static fn (Driver $d): bool => $d->getTeamId() === $teamId));
    }

    public function lockById(int $id): ?Driver
    {
        return $this->findById($id);
    }

    public function updateSuspension(int $id, bool $isSuspended): void
    {
        $d = $this->rows[$id];
        $this->rows[$id] = new Driver(
            $d->getId(),
            $d->getTeamId(),
            $d->getFirstName(),
            $d->getLastName(),
            $d->getCode(),
            $d->getRaceNumber(),
            $isSuspended,
        );
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
