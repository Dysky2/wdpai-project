<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Model\StandingRow;
use App\Repository\IncidentListRepositoryInterface;
use App\Repository\StandingRepositoryInterface;
use App\Service\LicenceService;
use App\Tests\Support\FixedClock;
use App\Tests\Support\FixedLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Progress bar data of the Super Licence table follows the LicenceStatus thresholds (no thresholds in the view). */
final class LicenceServiceTest extends TestCase
{
    private function rowFor(int $points): array
    {
        $standings = $this->createStub(StandingRepositoryInterface::class);
        $standings->method('findAll')->willReturn([
            new StandingRow(1, 'VER', 'Max', 'Verstappen', 1, 2, 'Red Bull', $points >= 12, $points, null),
        ]);
        $incidents = $this->createStub(IncidentListRepositoryInterface::class);
        $incidents->method('countByStatus')->willReturn(['UNDER_INVESTIGATION' => 0]);

        $service = new LicenceService($standings, $incidents, new FixedClock(), FixedLanguage::translator());

        return $service->table(StandingRepositoryInterface::FILTER_ALL, '', 1)['rows'][0];
    }

    /** @return array<string, array{int, string, int}> */
    public static function points(): array
    {
        return [
            'clean' => [0, 'low', 0],
            'active' => [6, 'low', 6],
            'caution starts at 7' => [7, 'medium', 7],
            'caution ends at 8' => [8, 'medium', 8],
            'ban risk starts at 9' => [9, 'high', 9],
            'suspended' => [12, 'high', 12],
            'over the limit is capped' => [14, 'high', 12],
        ];
    }

    #[DataProvider('points')]
    public function testProgressToneAndStepFollowThePoints(int $points, string $tone, int $step): void
    {
        $row = $this->rowFor($points);

        self::assertSame($tone, $row['progressTone']);
        self::assertSame($step, $row['progressStep']);
    }
}
