<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Evidence;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\PenaltyType;

final class PdoIncidentDetailRepository extends AbstractPdoRepository implements IncidentDetailRepositoryInterface
{
    public function findById(int $id): ?IncidentDetail
    {
        $row = $this->fetchOne(
            'SELECT i.id, i.lap_number, i.description, i.status, i.created_at, '
            . 'gp.name AS gp_name, gp.circuit_name, gp.total_laps, '
            . 'acc.race_number AS acc_number, acc.code AS acc_code, acc.first_name || \' \' || acc.last_name AS acc_name, '
            . 'acc.team_id AS acc_team_id, acc_t.name AS acc_team, '
            . 'aff.race_number AS aff_number, aff.code AS aff_code, aff.first_name || \' \' || aff.last_name AS aff_name, '
            . 'aff.team_id AS aff_team_id, aff_t.name AS aff_team, '
            . 'a.title AS article_title, a.reference AS article_reference, '
            . 'u.first_name || \' \' || u.last_name AS reporter_name, u.team_id AS reporter_team_id, rep_t.name AS reporter_team, '
            . 'p.penalty_type, p.sanction_value, p.points_added, p.reason AS penalty_reason, '
            . 'pa.title AS penalty_article_title, pa.reference AS penalty_article_reference '
            . 'FROM incidents i '
            . 'JOIN grands_prix gp ON gp.id = i.grand_prix_id '
            . 'JOIN drivers acc ON acc.id = i.accused_driver_id '
            . 'JOIN teams acc_t ON acc_t.id = acc.team_id '
            . 'LEFT JOIN drivers aff ON aff.id = i.affected_driver_id '
            . 'LEFT JOIN teams aff_t ON aff_t.id = aff.team_id '
            . 'JOIN infringement_articles a ON a.id = i.infringement_article_id '
            . 'JOIN users u ON u.id = i.reporting_user_id '
            . 'JOIN teams rep_t ON rep_t.id = u.team_id '
            . 'LEFT JOIN LATERAL (SELECT * FROM penalties WHERE incident_id = i.id ORDER BY id LIMIT 1) p ON TRUE '
            . 'LEFT JOIN infringement_articles pa ON pa.id = p.article_id '
            . 'WHERE i.id = :id',
            ['id' => $id]
        );

        if ($row === null) {
            return null;
        }

        return new IncidentDetail(
            id: (int) $row['id'],
            grandPrixName: (string) $row['gp_name'],
            circuitName: (string) $row['circuit_name'],
            totalLaps: (int) $row['total_laps'],
            lapNumber: (int) $row['lap_number'],
            accused: $this->driver($row, 'acc'),
            affected: $row['aff_code'] === null ? null : $this->driver($row, 'aff'),
            articleTitle: (string) $row['article_title'],
            articleReference: (string) $row['article_reference'],
            description: (string) $row['description'],
            status: IncidentStatus::from((string) $row['status']),
            reportedAt: $this->toDate($row['created_at']),
            reporterName: (string) $row['reporter_name'],
            reporterTeam: (string) $row['reporter_team'],
            reporterTeamId: (int) $row['reporter_team_id'],
            evidence: $this->findEvidence($id),
            penalty: $row['penalty_type'] === null ? null : [
                'type' => PenaltyType::from((string) $row['penalty_type']),
                'value' => $this->toNullableInt($row['sanction_value']),
                'points' => (int) $row['points_added'],
                'reason' => (string) $row['penalty_reason'],
                'articleTitle' => (string) $row['penalty_article_title'],
                'articleReference' => (string) $row['penalty_article_reference'],
            ],
        );
    }

    private function findEvidence(int $incidentId): ?Evidence
    {
        $row = $this->fetchOne(
            'SELECT id, incident_id, file_path, original_name, mime_type, size_bytes, sha256, uploaded_at '
            . 'FROM incident_evidence WHERE incident_id = :id ORDER BY id LIMIT 1',
            ['id' => $incidentId]
        );

        return $row === null ? null : new Evidence(
            (int) $row['id'],
            (int) $row['incident_id'],
            (string) $row['file_path'],
            (string) $row['original_name'],
            (string) $row['mime_type'],
            (int) $row['size_bytes'],
            (string) $row['sha256'],
            $this->toDate($row['uploaded_at']),
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array{number: int, code: string, name: string, team: string, teamId: int}
     */
    private function driver(array $row, string $prefix): array
    {
        return [
            'number' => (int) $row[$prefix . '_number'],
            'code' => (string) $row[$prefix . '_code'],
            'name' => (string) $row[$prefix . '_name'],
            'team' => (string) $row[$prefix . '_team'],
            'teamId' => (int) $row[$prefix . '_team_id'],
        ];
    }
}
