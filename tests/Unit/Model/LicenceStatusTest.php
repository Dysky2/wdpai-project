<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model;

use App\Model\LicenceStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LicenceStatusTest extends TestCase
{
    /** @return array<string, array{int, LicenceStatus}> */
    public static function thresholds(): array
    {
        return [
            '0 points' => [0, LicenceStatus::Clean],
            '1 point' => [1, LicenceStatus::Active],
            '6 points' => [6, LicenceStatus::Active],
            '7 points' => [7, LicenceStatus::Caution],
            '8 points' => [8, LicenceStatus::Caution],
            '9 points' => [9, LicenceStatus::AtBanRisk],
            '11 points' => [11, LicenceStatus::AtBanRisk],
            '12 points' => [12, LicenceStatus::Suspended],
            '15 points' => [15, LicenceStatus::Suspended],
        ];
    }

    #[DataProvider('thresholds')]
    public function testStatusFollowsActivePoints(int $points, LicenceStatus $expected): void
    {
        self::assertSame($expected, LicenceStatus::fromPoints($points));
    }

    public function testNegativeBalanceIsTreatedAsClean(): void
    {
        self::assertSame(LicenceStatus::Clean, LicenceStatus::fromPoints(-1));
    }
}
