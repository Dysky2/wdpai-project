<?php

declare(strict_types=1);

namespace App\Service;

/** An evidence file the current user may download: its stored name and the Content-Type to send. */
final class EvidenceFile
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $mimeType,
    ) {
    }
}
