<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Exception\NotFoundException;
use App\Model\Evidence;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\Role;
use App\Model\User;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentListRepositoryInterface;
use App\Service\EvidenceService;
use App\Service\IncidentService;
use App\Service\Upload\UploadService;
use App\Tests\Support\FakeTransactionRunner;
use App\Tests\Support\FakeUploadedFileHandler;
use App\Tests\Support\Fixtures;
use App\Tests\Support\FixedClock;
use App\Tests\Support\FixedLanguage;
use App\Tests\Support\InMemoryArticleRepository;
use App\Tests\Support\InMemoryDriverRepository;
use App\Tests\Support\InMemoryEvidenceRepository;
use App\Tests\Support\InMemoryGrandPrixRepository;
use App\Tests\Support\InMemoryIncidentRepository;
use App\Tests\Support\InMemoryTeamRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Who may download an evidence image: the same users who may open its case. */
final class EvidenceServiceTest extends TestCase
{
    private const FERRARI = 3;
    private const INCIDENT = 42;
    private const PNG = '0123456789abcdef0123456789abcdef.png';
    private const JPG = 'fedcba9876543210fedcba9876543210.jpg';

    private string $uploadDir;
    private InMemoryEvidenceRepository $evidence;
    private EvidenceService $service;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/apex_evidence_' . bin2hex(random_bytes(4));
        mkdir($this->uploadDir);

        $this->evidence = new InMemoryEvidenceRepository();
        foreach ([self::PNG, self::JPG] as $name) {
            file_put_contents($this->uploadDir . '/' . $name, 'image bytes');
            $this->evidence->save(new Evidence(
                null, self::INCIDENT, UploadService::PUBLIC_PREFIX . $name, 'onboard', 'image/png', 11, str_repeat('a', 64), Fixtures::now()
            ));
        }

        // Case 42: reported by Mercedes, accused driver from Red Bull.
        $details = $this->createStub(IncidentDetailRepositoryInterface::class);
        $details->method('findById')->willReturnCallback(
            static fn (int $id): ?IncidentDetail => $id === self::INCIDENT ? self::detail() : null
        );

        $uploads = new UploadService($this->uploadDir, new FakeUploadedFileHandler());
        $incidents = new IncidentService(
            $this->createStub(IncidentListRepositoryInterface::class),
            new InMemoryGrandPrixRepository(Fixtures::grandPrix()),
            $details,
            new InMemoryIncidentRepository(),
            new FakeTransactionRunner(),
            new InMemoryDriverRepository(),
            new InMemoryArticleRepository(Fixtures::article()),
            $this->evidence,
            new InMemoryTeamRepository(Fixtures::team()),
            $uploads,
            new FixedClock(),
            FixedLanguage::translator(),
        );

        $this->service = new EvidenceService($this->evidence, $incidents, $uploads);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->uploadDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->uploadDir);
    }

    private static function detail(): IncidentDetail
    {
        return new IncidentDetail(
            self::INCIDENT, 'Monaco GP', 'Circuit de Monaco', 57, 10,
            ['number' => 1, 'code' => 'VER', 'name' => 'Max Verstappen', 'team' => 'Red Bull', 'teamId' => Fixtures::RED_BULL],
            null, 'Collision', 'Art. 33.4', 'Opis incydentu wystarczająco długi', IncidentStatus::REPORTED,
            Fixtures::now(), 'Toto Wolff', 'Mercedes', Fixtures::MERCEDES, null, null,
        );
    }

    /** @return array<string, array{User}> */
    public static function allowedUsers(): array
    {
        return [
            'steward' => [Fixtures::steward()],
            'principal of the reporting team' => [Fixtures::principal(Fixtures::MERCEDES)],
            'principal of the accused team' => [Fixtures::principal(Fixtures::RED_BULL)],
        ];
    }

    #[DataProvider('allowedUsers')]
    public function testUserWhoMayOpenTheCaseGetsTheFile(User $user): void
    {
        $file = $this->service->fileForUser($user, self::PNG);

        self::assertSame(self::PNG, $file->fileName);
        self::assertSame('image/png', $file->mimeType);
    }

    public function testJpegGetsJpegContentType(): void
    {
        self::assertSame('image/jpeg', $this->service->fileForUser(Fixtures::steward(), self::JPG)->mimeType);
    }

    public function testPrincipalOfAnUninvolvedTeamGetsNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::principal(self::FERRARI), self::PNG);
    }

    public function testPrincipalWithoutTeamGetsNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(
            new User(5, 'x@team.f1', 'hash', Role::TEAM_PRINCIPAL, null, 'No', 'Team', Fixtures::now()),
            self::PNG,
        );
    }

    /** @return array<string, array{string}> */
    public static function invalidNames(): array
    {
        return [
            'parent directory' => ['../.env'],
            'encoded traversal' => ['%2e%2e%2f' . self::PNG],
            'nested path' => ['sub/' . self::PNG],
            'php script' => ['0123456789abcdef0123456789abcdef.php'],
            'double extension' => ['0123456789abcdef0123456789abcdef.php.png'],
            'null byte' => ["0123456789abcdef0123456789abcdef.png\0.php"],
            'trailing newline' => [self::PNG . "\n"],
            'upper-case hex' => ['0123456789ABCDEF0123456789ABCDEF.png'],
            'too short' => ['0123.png'],
            'empty' => [''],
        ];
    }

    #[DataProvider('invalidNames')]
    public function testNameThatIsNotAStoredFileNameIsNotFound(string $name): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::steward(), $name);
    }

    public function testFileUnknownToTheDatabaseIsNotFound(): void
    {
        file_put_contents($this->uploadDir . '/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png', 'orphan');
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::steward(), 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png');
    }

    public function testEvidenceOfAMissingCaseIsNotFound(): void
    {
        $name = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.png';
        file_put_contents($this->uploadDir . '/' . $name, 'image bytes');
        $this->evidence->save(new Evidence(
            null, 999, UploadService::PUBLIC_PREFIX . $name, 'x', 'image/png', 11, str_repeat('b', 64), Fixtures::now()
        ));
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::steward(), $name);
    }

    public function testFileMissingOnDiskIsNotFound(): void
    {
        unlink($this->uploadDir . '/' . self::PNG);
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::steward(), self::PNG);
    }

    public function testUninvolvedUserLearnsNothingAboutAMissingFile(): void
    {
        // Ownership is checked before the disk: another team gets 404 whether or not the file is still there.
        unlink($this->uploadDir . '/' . self::PNG);
        $this->expectException(NotFoundException::class);

        $this->service->fileForUser(Fixtures::principal(self::FERRARI), self::PNG);
    }
}
