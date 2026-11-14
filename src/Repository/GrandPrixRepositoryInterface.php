<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\GrandPrix;

interface GrandPrixRepositoryInterface
{
    public function findById(int $id): ?GrandPrix;

    /** @return list<GrandPrix> ordered by season and round */
    public function findAll(): array;

    /** The Grand Prix flagged as the active session (at most one). */
    public function findActive(): ?GrandPrix;
}
