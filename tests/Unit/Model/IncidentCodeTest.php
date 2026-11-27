<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model;

use App\Model\Incident;
use App\Model\IncidentStatus;
use App\Model\IncidentSummary;
use PHPUnit\Framework\TestCase;

/** The case code is formatted in one place (Incident::codeFor) and every read model uses it. */
final class IncidentCodeTest extends TestCase
{
    public function testIdIsPaddedToThreeDigits(): void
    {
        self::assertSame('INC-007', Incident::codeFor(7));
        self::assertSame('INC-042', Incident::codeFor(42));
    }

    public function testLongIdIsNotTruncated(): void
    {
        self::assertSame('INC-1234', Incident::codeFor(1234));
    }

    public function testUnsavedIncidentHasZeroCode(): void
    {
        $now = new \DateTimeImmutable('2026-01-01');
        $incident = new Incident(null, 1, 1, 1, null, 1, 1, 'text', IncidentStatus::REPORTED, $now, $now);

        self::assertSame('INC-000', $incident->getCode());
        self::assertSame('INC-015', $incident->withId(15)->getCode());
    }

    public function testReadModelUsesTheSameFormat(): void
    {
        $summary = new IncidentSummary(42, 3, [], 'Title', 'Description', IncidentStatus::REPORTED, null, null);

        self::assertSame(Incident::codeFor(42), $summary->getCode());
    }
}
