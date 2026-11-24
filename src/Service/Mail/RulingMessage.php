<?php

declare(strict_types=1);

namespace App\Service\Mail;

/** Everything the e-mail templates need; built from a committed penalty. */
final readonly class RulingMessage
{
    /** @param list<string> $recipients */
    public function __construct(
        public array $recipients,
        public string $incidentCode,
        public string $driverName,
        public string $article,
        public string $sanction,
        public int $points,
        public string $reason,
    ) {
    }
}
