<?php

declare(strict_types=1);

namespace App\Service\Command;

/** Input of PenaltyService::issuePenalty (raw form values; the service validates them). */
final readonly class IssuePenaltyCommand
{
    public function __construct(
        public int $incidentId,
        public int $articleId,
        public string $sanction,
        public int $points,
        public string $reason,
        public bool $notifyTeam,
    ) {
    }
}
