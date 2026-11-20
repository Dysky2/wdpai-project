<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\GrandPrix;
use App\Repository\GrandPrixRepositoryInterface;

final class InMemoryGrandPrixRepository implements GrandPrixRepositoryInterface
{
    public function __construct(private readonly GrandPrix $grandPrix)
    {
    }

    public function findById(int $id): ?GrandPrix
    {
        return $id === $this->grandPrix->getId() ? $this->grandPrix : null;
    }

    public function findAll(): array
    {
        return [$this->grandPrix];
    }

    public function findActive(): ?GrandPrix
    {
        return $this->grandPrix;
    }
}
