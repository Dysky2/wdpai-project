<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Team;

final class PdoTeamRepository extends AbstractPdoRepository implements TeamRepositoryInterface
{
    public function findById(int $id): ?Team
    {
        $row = $this->fetchOne('SELECT id, name, license_country FROM teams WHERE id = :id', ['id' => $id]);

        return $row === null ? null : new Team((int) $row['id'], (string) $row['name'], (string) $row['license_country']);
    }
}
