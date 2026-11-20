<?php

declare(strict_types=1);

namespace App\Tests\Support;

/** In-memory store that can be rolled back to a snapshot taken at the start of a transaction. */
interface Restorable
{
    public function snapshot(): mixed;

    public function restore(mixed $snapshot): void;
}
