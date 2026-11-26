<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Evidence;

final class PdoEvidenceRepository extends AbstractPdoRepository implements EvidenceRepositoryInterface
{
    private const COLUMNS = 'id, incident_id, file_path, original_name, mime_type, size_bytes, sha256, uploaded_at';

    public function findByIncidentId(int $incidentId): array
    {
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM incident_evidence WHERE incident_id = :incident ORDER BY id',
            ['incident' => $incidentId]
        );

        return array_map($this->toEvidence(...), $rows);
    }

    public function findByFilePath(string $filePath): ?Evidence
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM incident_evidence WHERE file_path = :path',
            ['path' => $filePath]
        );

        return $row === null ? null : $this->toEvidence($row);
    }

    public function save(Evidence $evidence): Evidence
    {
        $id = $this->insertReturningId(
            'INSERT INTO incident_evidence (incident_id, file_path, original_name, mime_type, size_bytes, sha256) '
            . 'VALUES (:incident, :path, :name, :mime, :size, :sha) RETURNING id',
            [
                'incident' => $evidence->getIncidentId(),
                'path' => $evidence->getFilePath(),
                'name' => $evidence->getOriginalName(),
                'mime' => $evidence->getMimeType(),
                'size' => $evidence->getSizeBytes(),
                'sha' => $evidence->getSha256(),
            ]
        );

        return $evidence->withId($id);
    }

    /** @param array<string, mixed> $row */
    private function toEvidence(array $row): Evidence
    {
        return new Evidence(
            (int) $row['id'],
            (int) $row['incident_id'],
            (string) $row['file_path'],
            (string) $row['original_name'],
            (string) $row['mime_type'],
            (int) $row['size_bytes'],
            trim((string) $row['sha256']),
            $this->toDate($row['uploaded_at']),
        );
    }
}
