<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnprocessableEntityException;
use App\Exception\ValidationException;
use App\Model\IncidentStatus;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentListRepositoryInterface;
use App\Service\Command\ReportIncidentCommand;
use App\Service\IncidentService;
use App\Service\Upload\UploadService;
use App\Tests\Support\FakeTransactionRunner;
use App\Tests\Support\FakeUploadedFileHandler;
use App\Tests\Support\Fixtures;
use App\Tests\Support\FixedClock;
use App\Tests\Support\InMemoryArticleRepository;
use App\Tests\Support\InMemoryDriverRepository;
use App\Tests\Support\InMemoryEvidenceRepository;
use App\Tests\Support\InMemoryGrandPrixRepository;
use App\Tests\Support\InMemoryIncidentRepository;
use App\Tests\Support\InMemoryTeamRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IncidentServiceTest extends TestCase
{
    private const OWN_DRIVER = 10;      // Mercedes
    private const OTHER_DRIVER = 20;    // Red Bull
    private const DESCRIPTION = 'Zepchnięcie z toru w zakręcie numer trzy.';

    private InMemoryIncidentRepository $incidents;
    private InMemoryEvidenceRepository $evidence;
    private FakeTransactionRunner $transactions;
    private IncidentService $service;
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/apex_incident_' . bin2hex(random_bytes(4));
        mkdir($this->uploadDir);

        $this->incidents = new InMemoryIncidentRepository();
        $this->evidence = new InMemoryEvidenceRepository();
        $this->transactions = new FakeTransactionRunner($this->incidents, $this->evidence);

        $this->service = new IncidentService(
            $this->createStub(IncidentListRepositoryInterface::class),
            new InMemoryGrandPrixRepository(Fixtures::grandPrix(1, 57)),
            $this->createStub(IncidentDetailRepositoryInterface::class),
            $this->incidents,
            $this->transactions,
            new InMemoryDriverRepository(
                Fixtures::driver(self::OWN_DRIVER, Fixtures::MERCEDES),
                Fixtures::driver(self::OTHER_DRIVER, Fixtures::RED_BULL),
            ),
            new InMemoryArticleRepository(Fixtures::article(1)),
            $this->evidence,
            new InMemoryTeamRepository(Fixtures::team(Fixtures::MERCEDES)),
            new UploadService($this->uploadDir, new FakeUploadedFileHandler()),
            new FixedClock(),
        );
    }

    protected function tearDown(): void
    {
        foreach (glob($this->uploadDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->uploadDir);
    }

    private function command(
        int $lap = 10,
        int $accused = self::OTHER_DRIVER,
        string $description = self::DESCRIPTION,
        ?array $file = null,
    ): ReportIncidentCommand {
        return new ReportIncidentCommand(1, $lap, $accused, null, 1, $description, $file);
    }

    /** @return array<string, array{key: string, params: array<string, scalar>}> */
    private function errorsOf(callable $call): array
    {
        try {
            $call();
        } catch (ValidationException $e) {
            return $e->getErrors();
        }
        self::fail('ValidationException expected');
    }

    public function testValidProtestIsSavedAsReported(): void
    {
        $incident = $this->service->report(Fixtures::principal(), $this->command());

        self::assertSame(100, $incident->getId());
        self::assertSame(IncidentStatus::REPORTED, $incident->getStatus());
        self::assertSame(self::OTHER_DRIVER, $incident->getAccusedDriverId());
        self::assertSame(1, $this->transactions->commits);
        self::assertCount(1, $this->incidents->rows);
    }

    public function testAccusedDriverFromOwnTeamIsRejected(): void
    {
        $errors = $this->errorsOf(fn () => $this->service->report(Fixtures::principal(), $this->command(accused: self::OWN_DRIVER)));

        self::assertSame('validation.accused_own_team', $errors['accused_driver_id']['key']);
        self::assertSame([], $this->incidents->rows);
    }

    /** @return array<string, array{int}> */
    public static function badLaps(): array
    {
        return ['zero' => [0], 'negative' => [-3], 'above the race distance' => [58]];
    }

    #[DataProvider('badLaps')]
    public function testLapNumberOutsideTheRaceIsRejected(int $lap): void
    {
        $errors = $this->errorsOf(fn () => $this->service->report(Fixtures::principal(), $this->command(lap: $lap)));

        self::assertSame('validation.lap_range', $errors['lap_number']['key']);
        self::assertSame(57, $errors['lap_number']['params']['max']);
        self::assertSame([], $this->incidents->rows);
    }

    public function testLastLapOfTheRaceIsAccepted(): void
    {
        $incident = $this->service->report(Fixtures::principal(), $this->command(lap: 57));

        self::assertSame(57, $incident->getLapNumber());
    }

    public function testTooShortDescriptionIsRejected(): void
    {
        $errors = $this->errorsOf(fn () => $this->service->report(Fixtures::principal(), $this->command(description: 'za krótko')));

        self::assertSame('validation.description_length', $errors['description']['key']);
    }

    public function testStewardCannotFileAProtest(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->service->report(Fixtures::steward(), $this->command());
    }

    public function testStoredFileIsRemovedWhenTheDatabaseWriteFails(): void
    {
        $png = $this->uploadDir . '/source.png';
        $image = imagecreatetruecolor(4, 4);
        imagepng($image, $png);
        $this->evidence->failOnSave = new \RuntimeException('db down');

        try {
            $this->service->report(Fixtures::principal(), $this->command(file: [
                'name' => 'clip.png', 'tmp_name' => $png, 'error' => UPLOAD_ERR_OK, 'size' => filesize($png),
            ]));
            self::fail('The database failure must propagate');
        } catch (\RuntimeException) {
            // expected
        }

        self::assertSame([], $this->incidents->rows, 'the incident row is rolled back');
        self::assertSame(1, $this->transactions->rollbacks);
        self::assertSame([], glob($this->uploadDir . '/*') ?: [], 'the orphaned file is deleted');
    }

    public function testInvestigateMovesReportedCaseToUnderInvestigation(): void
    {
        $this->incidents->add(Fixtures::incident(5, IncidentStatus::REPORTED));

        $result = $this->service->investigate(5);

        self::assertSame('UNDER_INVESTIGATION', $result['status']);
        self::assertSame('INC-005', $result['code']);
        self::assertSame(IncidentStatus::UNDER_INVESTIGATION, $this->incidents->rows[5]->getStatus());
    }

    public function testDismissMovesInvestigatedCaseToNoFurtherAction(): void
    {
        $this->incidents->add(Fixtures::incident(5, IncidentStatus::UNDER_INVESTIGATION));

        $result = $this->service->dismiss(5);

        self::assertSame('NO_FURTHER_ACTION', $result['status']);
    }

    /** @return array<string, array{IncidentStatus, string}> */
    public static function forbiddenTransitions(): array
    {
        return [
            'investigate a case that is already closed' => [IncidentStatus::PENALTY_ISSUED, 'investigate'],
            'investigate twice' => [IncidentStatus::UNDER_INVESTIGATION, 'investigate'],
            'dismiss without investigation' => [IncidentStatus::REPORTED, 'dismiss'],
            'dismiss a case with a penalty' => [IncidentStatus::PENALTY_ISSUED, 'dismiss'],
        ];
    }

    #[DataProvider('forbiddenTransitions')]
    public function testForbiddenTransitionIsRejectedAndStatusStays(IncidentStatus $from, string $action): void
    {
        $this->incidents->add(Fixtures::incident(5, $from));

        try {
            $this->service->{$action}(5);
            self::fail('UnprocessableEntityException expected');
        } catch (UnprocessableEntityException $e) {
            self::assertSame(422, $e->getStatusCode());
        }

        self::assertSame($from, $this->incidents->rows[5]->getStatus());
    }

    public function testTransitionOfUnknownCaseGivesNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->investigate(999);
    }
}
