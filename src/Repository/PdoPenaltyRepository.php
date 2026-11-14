<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Penalty;
use App\Model\PenaltyType;

final class PdoPenaltyRepository extends AbstractPdoRepository implements PenaltyRepositoryInterface
{
    private const COLUMNS = 'id, incident_id, driver_id, steward_id, article_id, penalty_type, sanction_value, '
        . 'points_added, reason, issued_at, expires_at, is_expired';

    /** Same definition of an active penalty as the view v_super_licence_standings. */
    private const ACTIVE = 'NOT is_expired AND expires_at > now()';

    public function findById(int $id): ?Penalty
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM penalties WHERE id = :id', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findByIncidentId(int $incidentId): array
    {
        return array_map(
            fn (array $row): Penalty => $this->hydrate($row),
            $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM penalties WHERE incident_id = :incident ORDER BY id',
                ['incident' => $incidentId]
            )
        );
    }

    public function findActiveByDriverId(int $driverId): array
    {
        return array_map(
            fn (array $row): Penalty => $this->hydrate($row),
            $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM penalties WHERE driver_id = :driver AND ' . self::ACTIVE
                . ' ORDER BY expires_at',
                ['driver' => $driverId]
            )
        );
    }

    public function sumActivePoints(int $driverId): int
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(SUM(points_added), 0) AS points FROM penalties WHERE driver_id = :driver AND '
            . self::ACTIVE,
            ['driver' => $driverId]
        );

        return (int) ($row['points'] ?? 0);
    }

    public function save(Penalty $penalty): Penalty
    {
        $id = $this->insertReturningId(
            'INSERT INTO penalties (incident_id, driver_id, steward_id, article_id, penalty_type, sanction_value, '
            . 'points_added, reason, issued_at, expires_at, is_expired) '
            . 'VALUES (:incident, :driver, :steward, :article, :type, :value, :points, :reason, :issued, :expires, :expired) '
            . 'RETURNING id',
            [
                'incident' => $penalty->getIncidentId(),
                'driver' => $penalty->getDriverId(),
                'steward' => $penalty->getStewardId(),
                'article' => $penalty->getArticleId(),
                'type' => $penalty->getType()->value,
                'value' => $penalty->getSanctionValue(),
                'points' => $penalty->getPointsAdded(),
                'reason' => $penalty->getReason(),
                'issued' => $penalty->getIssuedAt()->format('Y-m-d H:i:s.uP'),
                'expires' => $penalty->getExpiresAt()->format('Y-m-d H:i:s.uP'),
                'expired' => $penalty->isExpired(),
            ]
        );

        return $penalty->withId($id);
    }

    public function expireOutdated(\DateTimeImmutable $now): array
    {
        $rows = $this->fetchAll(
            'UPDATE penalties SET is_expired = TRUE WHERE NOT is_expired AND expires_at <= :now '
            . 'RETURNING driver_id, points_added',
            ['now' => $now->format('Y-m-d H:i:s.uP')]
        );

        return array_map(
            static fn (array $row): array => ['driverId' => (int) $row['driver_id'], 'points' => (int) $row['points_added']],
            $rows
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Penalty
    {
        return new Penalty(
            (int) $row['id'],
            (int) $row['incident_id'],
            (int) $row['driver_id'],
            (int) $row['steward_id'],
            (int) $row['article_id'],
            PenaltyType::from((string) $row['penalty_type']),
            $this->toNullableInt($row['sanction_value']),
            (int) $row['points_added'],
            (string) $row['reason'],
            $this->toDate($row['issued_at']),
            $this->toDate($row['expires_at']),
            (bool) $row['is_expired'],
        );
    }
}
