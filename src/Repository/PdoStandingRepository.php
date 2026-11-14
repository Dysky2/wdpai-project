<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\LicenceStatus;
use App\Model\StandingRow;

final class PdoStandingRepository extends AbstractPdoRepository implements StandingRepositoryInterface
{
    public function findAll(string $filter = self::FILTER_ALL, ?string $query = null): array
    {
        $conditions = [];
        $params = [];

        if ($filter === self::FILTER_RISK) {
            $conditions[] = 'active_points >= :threshold';
            $params['threshold'] = LicenceStatus::AT_RISK_THRESHOLD;
        } elseif ($filter === self::FILTER_CLEAN) {
            $conditions[] = 'active_points = 0';
        }

        $query = $query === null ? '' : trim($query);
        if ($query !== '') {
            $conditions[] = "(first_name || ' ' || last_name ILIKE :q OR code ILIKE :q OR team_name ILIKE :q "
                . 'OR race_number::text = :number)';
            $params['q'] = '%' . addcslashes($query, '%_\\') . '%';
            $params['number'] = $query;
        }

        $sql = 'SELECT driver_id, code, first_name, last_name, race_number, team_id, team_name, is_suspended, '
            . 'active_points, next_expiration FROM v_super_licence_standings'
            . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY active_points DESC, last_name';

        return array_map(
            fn (array $row): StandingRow => new StandingRow(
                (int) $row['driver_id'],
                trim((string) $row['code']),
                (string) $row['first_name'],
                (string) $row['last_name'],
                (int) $row['race_number'],
                (int) $row['team_id'],
                (string) $row['team_name'],
                (bool) $row['is_suspended'],
                (int) $row['active_points'],
                $this->toNullableDate($row['next_expiration']),
            ),
            $this->fetchAll($sql, $params)
        );
    }
}
