<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Runs a unit of work atomically (commit on success, rollback on any exception).
 */
interface TransactionRunnerInterface
{
    /**
     * @template T
     * @param callable(): T $work
     * @return T
     */
    public function transactional(callable $work): mixed;
}
