<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Team;
use App\Repository\TeamRepositoryInterface;

final class InMemoryTeamRepository implements TeamRepositoryInterface
{
    public function __construct(private readonly Team $team)
    {
    }

    public function findById(int $id): ?Team
    {
        return $id === $this->team->getId() ? $this->team : null;
    }
}
