<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Super Licence status derived from the number of active penalty points
 * (12 points within 365 days = suspension).
 */
enum LicenceStatus: string
{
    case Suspended = 'SUSPENDED';
    case AtBanRisk = 'AT_BAN_RISK';
    case Caution = 'CAUTION';
    case Active = 'ACTIVE';
    case Clean = 'CLEAN';

    public const POINTS_LIMIT = 12;
    public const AT_RISK_THRESHOLD = 7;

    public static function fromPoints(int $points): self
    {
        return match (true) {
            $points >= 12 => self::Suspended,
            $points >= 9 => self::AtBanRisk,
            $points >= 7 => self::Caution,
            $points >= 1 => self::Active,
            default => self::Clean,
        };
    }
}
