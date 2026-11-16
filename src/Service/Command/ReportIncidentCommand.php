<?php

declare(strict_types=1);

namespace App\Service\Command;

/**
 * Input of IncidentService::report. Ids are 0 when the submitted value was not a positive integer,
 * which the service rejects like any other unknown id.
 */
final readonly class ReportIncidentCommand
{
    /** @param ?array{name?: mixed, tmp_name?: mixed, error?: mixed, size?: mixed} $evidenceFile entry of $_FILES, if any */
    public function __construct(
        public int $grandPrixId,
        public int $lapNumber,
        public int $accusedDriverId,
        public ?int $affectedDriverId,
        public int $infringementArticleId,
        public string $description,
        public ?array $evidenceFile,
    ) {
    }
}
