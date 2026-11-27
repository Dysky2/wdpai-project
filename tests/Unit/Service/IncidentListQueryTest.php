<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\UnprocessableEntityException;
use App\Model\IncidentCriteria;
use App\Service\IncidentListQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** One validation for GET /incidents and GET /api/incidents: the same values pass, the same values give 422. */
final class IncidentListQueryTest extends TestCase
{
    public function testMissingParametersGiveDefaults(): void
    {
        $query = IncidentListQuery::fromArray([]);

        self::assertSame(IncidentCriteria::GROUP_ALL, $query->getStatusGroup());
        self::assertNull($query->getGrandPrix());
        self::assertSame('', $query->getSearch());
        self::assertSame(1, $query->getPage());
    }

    public function testValidParametersAreKept(): void
    {
        $query = IncidentListQuery::fromArray(['status' => 'investigation', 'gp' => '3', 'q' => 'VER', 'page' => '2']);

        self::assertSame('investigation', $query->getStatusGroup());
        self::assertSame('3', $query->getGrandPrix());
        self::assertSame('VER', $query->getSearch());
        self::assertSame(2, $query->getPage());
    }

    /** @return array<string, array{string}> */
    public static function statusGroups(): array
    {
        return array_combine(IncidentCriteria::GROUPS, array_map(static fn (string $g): array => [$g], IncidentCriteria::GROUPS));
    }

    #[DataProvider('statusGroups')]
    public function testEveryStatusGroupIsAccepted(string $group): void
    {
        self::assertSame($group, IncidentListQuery::fromArray(['status' => $group])->getStatusGroup());
    }

    public function testAllGrandsPrixIsAccepted(): void
    {
        self::assertSame('all', IncidentListQuery::fromArray(['gp' => 'all'])->getGrandPrix());
    }

    public function testNonStringSearchIsIgnored(): void
    {
        self::assertSame('', IncidentListQuery::fromArray(['q' => ['x']])->getSearch());
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidQueries(): array
    {
        return [
            'unknown status' => [['status' => 'foo']],
            'enum value instead of group' => [['status' => 'REPORTED']],
            'upper-case group' => [['status' => 'ALL']],
            'empty status' => [['status' => '']],
            'status as array' => [['status' => ['all']]],
            'gp text' => [['gp' => 'monaco']],
            'gp negative' => [['gp' => '-1']],
            'gp empty' => [['gp' => '']],
            'page zero' => [['page' => '0']],
            'page text' => [['page' => 'abc']],
            'page negative' => [['page' => '-2']],
            'page as array' => [['page' => ['1']]],
        ];
    }

    /** @param array<string, mixed> $query */
    #[DataProvider('invalidQueries')]
    public function testInvalidValueGivesUnprocessableEntity(array $query): void
    {
        $this->expectException(UnprocessableEntityException::class);

        IncidentListQuery::fromArray($query);
    }

    public function testCriteriaRejectsUnknownStatusGroup(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new IncidentCriteria(null, null, 'foo');
    }

    public function testCriteriaAcceptsKnownStatusGroup(): void
    {
        self::assertSame('closed', (new IncidentCriteria(null, null, 'closed'))->getStatusGroup());
    }
}
