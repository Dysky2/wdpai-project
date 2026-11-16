<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Incident;
use App\Model\Penalty;

/** Result of a ruling: the saved penalty, the new active point balance of the driver and the mail status. */
final readonly class PenaltyOutcome
{
    public function __construct(
        public Incident $incident,
        public Penalty $penalty,
        public int $activePoints,
        public bool $suspended,
        public bool $mailSent,
    ) {
    }
}
