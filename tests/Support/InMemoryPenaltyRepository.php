<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\ClockInterface;
use App\Model\Penalty;
use App\Repository\PenaltyRepositoryInterface;

final class InMemoryPenaltyRepository implements PenaltyRepositoryInterface, Restorable
{
    /** @var array<int, Penalty> */
    public array $rows = [];
    private int $nextId = 1;

    /** When set, save() throws it, to simulate a failure in the middle of a transaction. */
    public ?\Throwable $failOnSave = null;

    /** When set, sumActivePoints() throws it (called after the penalty row and the status were written). */
    public ?\Throwable $failOnSum = null;

    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function findById(int $id): ?Penalty
    {
        return $this->rows[$id] ?? null;
    }

    public function findByIncidentId(int $incidentId): array
    {
        return array_values(array_filter($this->rows, static fn (Penalty $p): bool => $p->getIncidentId() === $incidentId));
    }

    public function findActiveByDriverId(int $driverId): array
    {
        $now = $this->clock->now();

        return array_values(array_filter(
            $this->rows,
            static fn (Penalty $p): bool => $p->getDriverId() === $driverId && !$p->isExpired() && $p->getExpiresAt() > $now,
        ));
    }

    public function sumActivePoints(int $driverId): int
    {
        if ($this->failOnSum !== null) {
            throw $this->failOnSum;
        }

        return array_sum(array_map(
            static fn (Penalty $p): int => $p->getPointsAdded(),
            $this->findActiveByDriverId($driverId),
        ));
    }

    public function save(Penalty $penalty): Penalty
    {
        if ($this->failOnSave !== null) {
            throw $this->failOnSave;
        }
        $saved = $penalty->withId($this->nextId++);
        $this->rows[(int) $saved->getId()] = $saved;

        return $saved;
    }

    public function expireOutdated(\DateTimeImmutable $now): array
    {
        return [];
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
