<?php

declare(strict_types=1);

namespace App\Service\Upload;

/**
 * Result of a successful evidence upload: the metadata that goes to incident_evidence.
 */
final readonly class StoredEvidence
{
    public function __construct(
        public string $filePath,
        public string $originalName,
        public string $mimeType,
        public int $sizeBytes,
        public string $sha256,
    ) {
    }
}
