<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Core\TransactionRunnerInterface;

/** Commits on success; on any exception restores every registered fake store (a real rollback in memory). */
final class FakeTransactionRunner implements TransactionRunnerInterface
{
    public int $commits = 0;
    public int $rollbacks = 0;

    /** @var list<Restorable> */
    private array $stores;

    public function __construct(Restorable ...$stores)
    {
        $this->stores = array_values($stores);
    }

    public function transactional(callable $work): mixed
    {
        $snapshots = array_map(static fn (Restorable $s): mixed => $s->snapshot(), $this->stores);

        try {
            $result = $work();
        } catch (\Throwable $e) {
            foreach ($this->stores as $i => $store) {
                $store->restore($snapshots[$i]);
            }
            $this->rollbacks++;
            throw $e;
        }

        $this->commits++;

        return $result;
    }
}
