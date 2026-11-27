<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Driver;
use App\Model\GrandPrix;
use App\Model\Incident;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\InfringementArticle;
use App\Model\PenaltyType;
use App\Model\Role;
use App\Model\Team;
use App\Model\User;

/** Builders for domain objects with sensible defaults. */
final class Fixtures
{
    public const MERCEDES = 1;
    public const RED_BULL = 2;

    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-06-01 12:00:00 UTC');
    }

    public static function principal(int $teamId = self::MERCEDES): User
    {
        return new User(1, 'toto@mercedes.f1', 'hash', Role::TEAM_PRINCIPAL, $teamId, 'Toto', 'Wolff', self::now());
    }

    public static function steward(): User
    {
        return new User(2, 'steward@fia.org', 'hash', Role::FIA_STEWARD, null, 'Niels', 'Wittich', self::now());
    }

    public static function driver(int $id, int $teamId, bool $suspended = false): Driver
    {
        return new Driver($id, $teamId, 'Driver', (string) $id, 'D' . $id, $id, $suspended);
    }

    public static function grandPrix(int $id = 1, int $laps = 57): GrandPrix
    {
        return new GrandPrix($id, 'Monaco GP', 'Circuit de Monaco', 'MC', 2026, 8, self::now(), $laps, true);
    }

    public static function article(int $id = 1): InfringementArticle
    {
        return new InfringementArticle($id, '33.4', 'Collision', 'Art. 33.4', PenaltyType::TIME_PENALTY, 5, 2, 'Precedent');
    }

    public static function team(int $id = self::MERCEDES): Team
    {
        return new Team($id, 'Team ' . $id, 'DE');
    }

    /** Case file read model; $affected = false leaves out the affected driver. */
    public static function incidentDetail(int $id, IncidentStatus $status, bool $affected = true): IncidentDetail
    {
        return new IncidentDetail(
            $id,
            'Monaco GP',
            'Circuit de Monaco',
            78,
            10,
            ['number' => 1, 'code' => 'VER', 'name' => 'Max Verstappen', 'team' => 'Red Bull', 'teamId' => self::RED_BULL],
            $affected ? ['number' => 44, 'code' => 'HAM', 'name' => 'Lewis Hamilton', 'team' => 'Mercedes', 'teamId' => self::MERCEDES] : null,
            'Collision',
            'Art. 33.4',
            'Opis incydentu wystarczająco długi',
            $status,
            self::now(),
            'Toto Wolff',
            'Mercedes',
            self::MERCEDES,
            null,
            null,
        );
    }

    public static function incident(int $id, IncidentStatus $status, int $accusedId = 20): Incident
    {
        return new Incident($id, 1, 1, $accusedId, null, 1, 10, 'Opis incydentu wystarczająco długi', $status, self::now(), self::now());
    }
}
