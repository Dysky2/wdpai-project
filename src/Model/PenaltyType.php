<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Kind of sanction; values match the CHECK constraints in the database.
 */
enum PenaltyType: string
{
    case TIME_PENALTY = 'TIME_PENALTY';
    case GRID_DROP = 'GRID_DROP';
    case DRIVE_THROUGH = 'DRIVE_THROUGH';
    case REPRIMAND = 'REPRIMAND';
    case DISQUALIFICATION = 'DISQUALIFICATION';

    /** Seconds (time penalty) or grid places (grid drop) are required for these types. */
    public function requiresValue(): bool
    {
        return $this === self::TIME_PENALTY || $this === self::GRID_DROP;
    }
}
