<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ForbiddenException;
use App\Exception\UnprocessableEntityException;
use App\Exception\ValidationException;
use App\Model\IncidentStatus;
use App\Model\Penalty;
use App\Model\PenaltyType;
use App\Service\Command\IssuePenaltyCommand;
use App\Service\PenaltyPolicy;
use App\Service\PenaltyService;
use App\Tests\Support\FakeLogger;
use App\Tests\Support\FakeMailer;
use App\Tests\Support\FakeTransactionRunner;
use App\Tests\Support\FixedClock;
use App\Tests\Support\Fixtures;
use App\Tests\Support\InMemoryArticleRepository;
use App\Tests\Support\InMemoryDriverRepository;
use App\Tests\Support\InMemoryIncidentRepository;
use App\Tests\Support\InMemoryPenaltyRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PenaltyServiceTest extends TestCase
{
    private const INCIDENT = 5;
    private const DRIVER = 20;

    private FixedClock $clock;
    private InMemoryIncidentRepository $incidents;
    private InMemoryDriverRepository $drivers;
    private InMemoryPenaltyRepository $penalties;
    private FakeTransactionRunner $transactions;
    private FakeLogger $logger;

    protected function setUp(): void
    {
        $this->clock = new FixedClock();
        $this->incidents = new InMemoryIncidentRepository();
        $this->incidents->add(Fixtures::incident(self::INCIDENT, IncidentStatus::UNDER_INVESTIGATION, self::DRIVER));
        $this->drivers = new InMemoryDriverRepository(Fixtures::driver(self::DRIVER, Fixtures::RED_BULL));
        $this->penalties = new InMemoryPenaltyRepository($this->clock);
        $this->transactions = new FakeTransactionRunner($this->incidents, $this->drivers, $this->penalties);
        $this->logger = new FakeLogger();
    }

    private function service(?FakeMailer $mailer = null): PenaltyService
    {
        return new PenaltyService(
            $this->incidents,
            $this->drivers,
            $this->penalties,
            new InMemoryArticleRepository(Fixtures::article(1)),
            $this->transactions,
            new PenaltyPolicy(),
            $mailer ?? new FakeMailer(),
            $this->logger,
            $this->clock,
        );
    }

    private function command(int $points = 2, bool $notify = false, string $sanction = 'TIME_PENALTY:5'): IssuePenaltyCommand
    {
        return new IssuePenaltyCommand(self::INCIDENT, 1, $sanction, $points, 'Wymuszenie kolizji.', $notify);
    }

    /** Gives the driver an active penalty worth $points (issued earlier, still within 365 days). */
    private function seedPoints(int $points): void
    {
        $issued = $this->clock->now()->modify('-30 days');
        $this->penalties->save(new Penalty(
            null, 900, self::DRIVER, 2, 1, PenaltyType::TIME_PENALTY, 5, $points, 'Wcześniejsza kara',
            $issued, (new PenaltyPolicy())->expiryFor($issued), false,
        ));
    }

    public function testPenaltyAddsPointsAndClosesTheCase(): void
    {
        $outcome = $this->service()->issuePenalty($this->command(points: 2), Fixtures::steward());

        self::assertSame(2, $outcome->activePoints);
        self::assertFalse($outcome->suspended);
        self::assertSame(PenaltyType::TIME_PENALTY, $outcome->penalty->getType());
        self::assertSame(5, $outcome->penalty->getSanctionValue());
        self::assertSame(IncidentStatus::PENALTY_ISSUED, $this->incidents->rows[self::INCIDENT]->getStatus());
        self::assertCount(1, $this->penalties->rows);
        self::assertSame(1, $this->transactions->commits);
    }

    public function testPenaltyExpiresAfter365Days(): void
    {
        $outcome = $this->service()->issuePenalty($this->command(), Fixtures::steward());

        self::assertEquals($this->clock->now()->modify('+365 days'), $outcome->penalty->getExpiresAt());
    }

    public function testReachingTwelvePointsSuspendsTheDriver(): void
    {
        $this->seedPoints(10);

        $outcome = $this->service()->issuePenalty($this->command(points: 2), Fixtures::steward());

        self::assertSame(12, $outcome->activePoints);
        self::assertTrue($outcome->suspended);
        self::assertTrue($this->drivers->findById(self::DRIVER)?->isSuspended());
    }

    public function testElevenPointsDoNotSuspendTheDriver(): void
    {
        $this->seedPoints(10);

        $outcome = $this->service()->issuePenalty($this->command(points: 1), Fixtures::steward());

        self::assertSame(11, $outcome->activePoints);
        self::assertFalse($outcome->suspended);
        self::assertFalse($this->drivers->findById(self::DRIVER)?->isSuspended());
    }

    public function testFailureInTheMiddleRollsEverythingBack(): void
    {
        // The penalty row and the new status are already written when the balance query fails.
        $this->penalties->failOnSum = new \RuntimeException('connection lost');

        try {
            $this->service()->issuePenalty($this->command(), Fixtures::steward());
            self::fail('The failure must propagate');
        } catch (\RuntimeException $e) {
            self::assertSame('connection lost', $e->getMessage());
        }

        self::assertSame(1, $this->transactions->rollbacks);
        self::assertSame(0, $this->transactions->commits);
        self::assertSame([], $this->penalties->rows, 'no penalty row survives');
        self::assertSame(
            IncidentStatus::UNDER_INVESTIGATION,
            $this->incidents->rows[self::INCIDENT]->getStatus(),
            'the case status is restored',
        );
        self::assertFalse($this->drivers->findById(self::DRIVER)?->isSuspended());
    }

    public function testMailFailureDoesNotUndoThePenalty(): void
    {
        $outcome = $this->service(new FakeMailer(fails: true))->issuePenalty($this->command(notify: true), Fixtures::steward());

        self::assertFalse($outcome->mailSent);
        self::assertCount(1, $this->penalties->rows, 'the penalty is committed');
        self::assertSame(IncidentStatus::PENALTY_ISSUED, $this->incidents->rows[self::INCIDENT]->getStatus());
        self::assertSame(1, $this->transactions->commits);
        self::assertCount(1, $this->logger->errors);
        self::assertStringContainsString('SMTP down', $this->logger->errors[0]);
    }

    public function testMailIsSentOnlyWhenRequested(): void
    {
        $mailer = new FakeMailer();
        $this->service($mailer)->issuePenalty($this->command(notify: false), Fixtures::steward());
        self::assertSame([], $mailer->sent);

        $this->setUp();
        $mailer = new FakeMailer();
        $outcome = $this->service($mailer)->issuePenalty($this->command(notify: true), Fixtures::steward());
        self::assertTrue($outcome->mailSent);
        self::assertCount(1, $mailer->sent);
    }

    public function testTeamPrincipalCannotIssueAPenalty(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->service()->issuePenalty($this->command(), Fixtures::principal());
    }

    public function testCaseThatIsNotUnderInvestigationCannotBeRuled(): void
    {
        $this->incidents->add(Fixtures::incident(self::INCIDENT, IncidentStatus::REPORTED, self::DRIVER));

        try {
            $this->service()->issuePenalty($this->command(), Fixtures::steward());
            self::fail('UnprocessableEntityException expected');
        } catch (UnprocessableEntityException) {
            // expected
        }

        self::assertSame([], $this->penalties->rows);
    }

    /** @return array<string, array{int, string, string}> */
    public static function invalidRulings(): array
    {
        return [
            'too many points' => [4, 'TIME_PENALTY:5', 'points'],
            'negative points' => [-1, 'TIME_PENALTY:5', 'points'],
            'unknown sanction' => [2, 'BANISHMENT', 'sanction'],
            'time penalty without value' => [2, 'TIME_PENALTY', 'sanction'],
        ];
    }

    #[DataProvider('invalidRulings')]
    public function testInvalidRulingIsRejectedWithFieldError(int $points, string $sanction, string $field): void
    {
        try {
            $this->service()->issuePenalty($this->command(points: $points, sanction: $sanction), Fixtures::steward());
            self::fail('ValidationException expected');
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->getErrors());
        }

        self::assertSame([], $this->penalties->rows);
        self::assertSame(0, $this->transactions->commits);
    }

    public function testPointsLeaveTheBalanceAfterTheyExpire(): void
    {
        $this->service()->issuePenalty($this->command(points: 3), Fixtures::steward());
        self::assertSame(3, $this->penalties->sumActivePoints(self::DRIVER));

        $this->clock->advanceDays(364);
        self::assertSame(3, $this->penalties->sumActivePoints(self::DRIVER));

        $this->clock->advanceDays(2);
        self::assertSame(0, $this->penalties->sumActivePoints(self::DRIVER));
    }
}
