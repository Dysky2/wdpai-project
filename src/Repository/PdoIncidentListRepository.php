<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\IncidentCriteria;
use App\Model\IncidentStatus;
use App\Model\IncidentSummary;
use App\Model\PenaltyType;

final class PdoIncidentListRepository extends AbstractPdoRepository implements IncidentListRepositoryInterface
{
    private const FROM = 'FROM incidents i '
        . 'JOIN users u ON u.id = i.reporting_user_id '
        . 'JOIN drivers acc ON acc.id = i.accused_driver_id '
        . 'LEFT JOIN drivers aff ON aff.id = i.affected_driver_id '
        . 'JOIN infringement_articles a ON a.id = i.infringement_article_id ';

    public function findPage(IncidentCriteria $criteria, int $limit, int $offset): array
    {
        [$where, $params] = $this->conditions($criteria, true);

        $rows = $this->fetchAll(
            'SELECT i.id, i.lap_number, i.description, i.status, a.title AS article_title, '
            . 'acc.race_number AS acc_number, acc.code AS acc_code, acc.first_name || \' \' || acc.last_name AS acc_name, '
            . 'aff.race_number AS aff_number, aff.code AS aff_code, aff.first_name || \' \' || aff.last_name AS aff_name, '
            . 'p.penalty_type, p.sanction_value '
            . self::FROM
            . 'LEFT JOIN LATERAL (SELECT penalty_type, sanction_value FROM penalties '
            . 'WHERE incident_id = i.id ORDER BY id LIMIT 1) p ON TRUE '
            . $where
            . ' ORDER BY i.id DESC LIMIT :limit OFFSET :offset',
            $params + ['limit' => $limit, 'offset' => $offset]
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function count(IncidentCriteria $criteria): int
    {
        [$where, $params] = $this->conditions($criteria, true);
        $row = $this->fetchOne('SELECT count(*) AS n ' . self::FROM . $where, $params);

        return (int) ($row['n'] ?? 0);
    }

    public function countByStatus(IncidentCriteria $criteria): array
    {
        [$where, $params] = $this->conditions($criteria, false);
        $rows = $this->fetchAll('SELECT i.status, count(*) AS n ' . self::FROM . $where . ' GROUP BY i.status', $params);

        $counts = [];
        foreach (IncidentStatus::cases() as $status) {
            $counts[$status->value] = 0;
        }
        foreach ($rows as $row) {
            $counts[(string) $row['status']] = (int) $row['n'];
        }

        return $counts;
    }

    /**
     * Builds the WHERE clause from fixed SQL fragments; every value travels as a bound parameter.
     *
     * @return array{0: string, 1: array<string, scalar|null>}
     */
    private function conditions(IncidentCriteria $criteria, bool $withFilters): array
    {
        $parts = [];
        $params = [];

        if ($criteria->getTeamScope() !== null) {
            $parts[] = '(u.team_id = :team_reporter OR acc.team_id = :team_accused)';
            $params['team_reporter'] = $criteria->getTeamScope();
            $params['team_accused'] = $criteria->getTeamScope();
        }

        if ($criteria->getGrandPrixId() !== null) {
            $parts[] = 'i.grand_prix_id = :gp';
            $params['gp'] = $criteria->getGrandPrixId();
        }

        if ($withFilters) {
            $placeholders = [];
            foreach ($criteria->getStatuses() as $index => $status) {
                $placeholders[] = ':status' . $index;
                $params['status' . $index] = $status->value;
            }
            if ($placeholders !== []) {
                $parts[] = 'i.status IN (' . implode(', ', $placeholders) . ')';
            }

            $search = trim($criteria->getSearch());
            if ($search !== '') {
                [$searchSql, $searchParams] = $this->searchCondition($search);
                $parts[] = $searchSql;
                $params += $searchParams;
            }
        }

        return [$parts === [] ? '' : 'WHERE ' . implode(' AND ', $parts), $params];
    }

    /**
     * Matches the case code (INC-042 or 42), driver code or name (accused or affected), article title and description.
     *
     * @return array{0: string, 1: array<string, scalar|null>}
     */
    private function searchCondition(string $search): array
    {
        // "!" is the LIKE escape character (a backslash would confuse PDO's placeholder parser).
        $like = '%' . preg_replace('/[!%_]/', '!$0', $search) . '%';
        $fields = ['acc.code', 'acc.first_name || \' \' || acc.last_name', 'aff.code',
            'aff.first_name || \' \' || aff.last_name', 'a.title', 'i.description'];

        $sql = [];
        $params = [];
        foreach ($fields as $index => $field) {
            $sql[] = '(' . $field . ') ILIKE :search' . $index . ' ESCAPE \'!\'';
            $params['search' . $index] = $like;
        }

        if (preg_match('/^(?:inc[-\s]?)?0*(\d+)$/i', $search, $matches) === 1) {
            $sql[] = 'i.id = :search_id';
            $params['search_id'] = (int) $matches[1];
        }

        return ['(' . implode(' OR ', $sql) . ')', $params];
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): IncidentSummary
    {
        $drivers = [['number' => (int) $row['acc_number'], 'code' => (string) $row['acc_code'], 'name' => (string) $row['acc_name']]];
        if ($row['aff_code'] !== null) {
            $drivers[] = ['number' => (int) $row['aff_number'], 'code' => (string) $row['aff_code'], 'name' => (string) $row['aff_name']];
        }

        return new IncidentSummary(
            id: (int) $row['id'],
            lapNumber: (int) $row['lap_number'],
            drivers: $drivers,
            articleTitle: (string) $row['article_title'],
            description: (string) $row['description'],
            status: IncidentStatus::from((string) $row['status']),
            penaltyType: $row['penalty_type'] === null ? null : PenaltyType::from((string) $row['penalty_type']),
            penaltyValue: $this->toNullableInt($row['sanction_value']),
        );
    }
}
