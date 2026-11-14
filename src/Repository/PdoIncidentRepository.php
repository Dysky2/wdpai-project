<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Incident;
use App\Model\IncidentStatus;

final class PdoIncidentRepository extends AbstractPdoRepository implements IncidentRepositoryInterface
{
    private const COLUMNS = 'id, grand_prix_id, reporting_user_id, accused_driver_id, affected_driver_id, '
        . 'infringement_article_id, lap_number, description, status, created_at, updated_at';

    public function findById(int $id): ?Incident
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM incidents WHERE id = :id', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findByIdForUpdate(int $id): ?Incident
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM incidents WHERE id = :id FOR UPDATE',
            ['id' => $id]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    public function save(Incident $incident): Incident
    {
        $id = $this->insertReturningId(
            'INSERT INTO incidents (grand_prix_id, reporting_user_id, accused_driver_id, affected_driver_id, '
            . 'infringement_article_id, lap_number, description, status) '
            . 'VALUES (:gp, :reporter, :accused, :affected, :article, :lap, :description, :status) RETURNING id',
            [
                'gp' => $incident->getGrandPrixId(),
                'reporter' => $incident->getReportingUserId(),
                'accused' => $incident->getAccusedDriverId(),
                'affected' => $incident->getAffectedDriverId(),
                'article' => $incident->getInfringementArticleId(),
                'lap' => $incident->getLapNumber(),
                'description' => $incident->getDescription(),
                'status' => $incident->getStatus()->value,
            ]
        );

        return $incident->withId($id);
    }

    public function updateStatus(int $id, IncidentStatus $status): void
    {
        $this->execute(
            'UPDATE incidents SET status = :status WHERE id = :id',
            ['status' => $status->value, 'id' => $id]
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Incident
    {
        return new Incident(
            (int) $row['id'],
            (int) $row['grand_prix_id'],
            (int) $row['reporting_user_id'],
            (int) $row['accused_driver_id'],
            $this->toNullableInt($row['affected_driver_id']),
            (int) $row['infringement_article_id'],
            (int) $row['lap_number'],
            (string) $row['description'],
            IncidentStatus::from((string) $row['status']),
            $this->toDate($row['created_at']),
            $this->toDate($row['updated_at']),
        );
    }
}
