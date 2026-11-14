<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Driver;

final class PdoDriverRepository extends AbstractPdoRepository implements DriverRepositoryInterface
{
    private const COLUMNS = 'id, team_id, first_name, last_name, code, race_number, is_suspended';

    public function findById(int $id): ?Driver
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM drivers WHERE id = :id', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findAll(): array
    {
        return array_map(
            fn (array $row): Driver => $this->hydrate($row),
            $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM drivers ORDER BY race_number')
        );
    }

    public function findByTeamId(int $teamId): array
    {
        return array_map(
            fn (array $row): Driver => $this->hydrate($row),
            $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM drivers WHERE team_id = :team ORDER BY race_number',
                ['team' => $teamId]
            )
        );
    }

    public function lockById(int $id): ?Driver
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM drivers WHERE id = :id FOR UPDATE',
            ['id' => $id]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    public function updateSuspension(int $id, bool $isSuspended): void
    {
        $this->execute(
            'UPDATE drivers SET is_suspended = :suspended WHERE id = :id',
            ['suspended' => $isSuspended, 'id' => $id]
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Driver
    {
        return new Driver(
            (int) $row['id'],
            (int) $row['team_id'],
            (string) $row['first_name'],
            (string) $row['last_name'],
            trim((string) $row['code']),
            (int) $row['race_number'],
            (bool) $row['is_suspended'],
        );
    }
}
