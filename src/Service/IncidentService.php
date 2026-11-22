<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\ClockInterface;
use App\Core\TransactionRunnerInterface;
use App\Core\Translator;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnprocessableEntityException;
use App\Exception\ValidationException;
use App\Model\Evidence;
use App\Model\Incident;
use App\Model\IncidentCriteria;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\IncidentSummary;
use App\Model\PenaltyType;
use App\Model\User;
use App\Repository\DriverRepositoryInterface;
use App\Repository\EvidenceRepositoryInterface;
use App\Repository\GrandPrixRepositoryInterface;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentListRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\InfringementArticleRepositoryInterface;
use App\Repository\TeamRepositoryInterface;
use App\Service\Command\ReportIncidentCommand;
use App\Service\Upload\InvalidUploadException;
use App\Service\Upload\UploadService;

/**
 * Incident log use cases. Ownership scope is decided here, never in the view:
 * a team principal sees cases where their team reported or is accused, a steward sees everything.
 */
final class IncidentService
{
    public const PER_PAGE = 6;

    /** Value of the "gp" query parameter that disables the Grand Prix filter. */
    public const ALL_GRANDS_PRIX = 'all';

    private const SUMMARY_LENGTH = 110;

    public function __construct(
        private readonly IncidentListRepositoryInterface $incidents,
        private readonly GrandPrixRepositoryInterface $grandsPrix,
        private readonly IncidentDetailRepositoryInterface $details,
        private readonly IncidentRepositoryInterface $incidentRepository,
        private readonly TransactionRunnerInterface $transactions,
        private readonly DriverRepositoryInterface $drivers,
        private readonly InfringementArticleRepositoryInterface $articles,
        private readonly EvidenceRepositoryInterface $evidence,
        private readonly TeamRepositoryInterface $teams,
        private readonly UploadService $uploads,
        private readonly ClockInterface $clock,
        private readonly Translator $translator,
    ) {
    }

    public const DESCRIPTION_MIN = 20;
    public const DESCRIPTION_MAX = 500;

    /**
     * Data for the protest form of the user's team.
     *
     * @throws ForbiddenException when the user is not a team principal bound to a team
     */
    public function reportForm(User $user): ReportForm
    {
        $teamId = $this->reportingTeamId($user);
        $team = $this->teams->findById($teamId) ?? throw new ForbiddenException('Unknown team');

        return new ReportForm(
            $team,
            $user->getDisplayName(),
            $this->grandsPrix->findAll(),
            $this->grandsPrix->findActive(),
            array_values(array_filter($this->drivers->findAll(), static fn ($d): bool => $d->getTeamId() !== $teamId)),
            $this->drivers->findByTeamId($teamId),
            $this->articles->findAll(),
        );
    }

    /**
     * Files a protest: validates the input, then saves the case and its evidence metadata in one transaction.
     * A stored file is deleted again when the database write fails.
     *
     * @throws ForbiddenException  when the user may not file protests
     * @throws ValidationException with per-field errors (HTTP 422)
     */
    public function report(User $user, ReportIncidentCommand $cmd): Incident
    {
        $teamId = $this->reportingTeamId($user);
        $errors = $this->validateReport($teamId, $cmd);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $stored = null;
        if ($this->hasFile($cmd->evidenceFile)) {
            try {
                $stored = $this->uploads->store($cmd->evidenceFile ?? []);
            } catch (InvalidUploadException $e) {
                throw new ValidationException(['evidence' => ['key' => $e->getErrorKey(), 'params' => $e->getParams()]]);
            }
        }

        try {
            return $this->transactions->transactional(function () use ($user, $cmd, $stored): Incident {
                $now = $this->clock->now();
                $incident = $this->incidentRepository->save(new Incident(
                    null,
                    $cmd->grandPrixId,
                    $user->getId(),
                    $cmd->accusedDriverId,
                    $cmd->affectedDriverId,
                    $cmd->infringementArticleId,
                    $cmd->lapNumber,
                    $cmd->description,
                    IncidentStatus::REPORTED,
                    $now,
                    $now,
                ));
                if ($stored !== null) {
                    $this->evidence->save(new Evidence(
                        null,
                        (int) $incident->getId(),
                        $stored->filePath,
                        $stored->originalName,
                        $stored->mimeType,
                        $stored->sizeBytes,
                        $stored->sha256,
                        $now,
                    ));
                }

                return $incident;
            });
        } catch (\Throwable $e) {
            if ($stored !== null) {
                $this->uploads->delete($stored);
            }
            throw $e;
        }
    }

    /** @return array<string, array{key: string, params: array<string, scalar>}> */
    private function validateReport(int $teamId, ReportIncidentCommand $cmd): array
    {
        $errors = [];
        $grandPrix = $cmd->grandPrixId > 0 ? $this->grandsPrix->findById($cmd->grandPrixId) : null;

        if ($grandPrix === null) {
            $errors['grand_prix_id'] = ['key' => 'validation.grand_prix_unknown', 'params' => []];
        } elseif ($cmd->lapNumber < 1 || $cmd->lapNumber > $grandPrix->getTotalLaps()) {
            $errors['lap_number'] = ['key' => 'validation.lap_range', 'params' => ['max' => $grandPrix->getTotalLaps()]];
        }

        $accused = $cmd->accusedDriverId > 0 ? $this->drivers->findById($cmd->accusedDriverId) : null;
        if ($accused === null) {
            $errors['accused_driver_id'] = ['key' => 'validation.accused_unknown', 'params' => []];
        } elseif ($accused->getTeamId() === $teamId) {
            $errors['accused_driver_id'] = ['key' => 'validation.accused_own_team', 'params' => []];
        }

        if ($cmd->affectedDriverId !== null) {
            $affected = $cmd->affectedDriverId > 0 ? $this->drivers->findById($cmd->affectedDriverId) : null;
            if ($affected === null) {
                $errors['affected_driver_id'] = ['key' => 'validation.affected_unknown', 'params' => []];
            } elseif ($affected->getTeamId() !== $teamId) {
                $errors['affected_driver_id'] = ['key' => 'validation.affected_not_own', 'params' => []];
            } elseif ($affected->getId() === $cmd->accusedDriverId) {
                $errors['affected_driver_id'] = ['key' => 'validation.affected_same_as_accused', 'params' => []];
            }
        }

        if ($cmd->infringementArticleId < 1 || $this->articles->findById($cmd->infringementArticleId) === null) {
            $errors['infringement_article_id'] = ['key' => 'validation.article_unknown', 'params' => []];
        }

        // Text that is not valid UTF-8 would be rejected by PostgreSQL (HTTP 500), so it is refused here.
        $length = mb_strlen($cmd->description);
        if (!mb_check_encoding($cmd->description, 'UTF-8') || $length < self::DESCRIPTION_MIN || $length > self::DESCRIPTION_MAX) {
            $errors['description'] = [
                'key' => 'validation.description_length',
                'params' => ['min' => self::DESCRIPTION_MIN, 'max' => self::DESCRIPTION_MAX],
            ];
        }

        return $errors;
    }

    /** True when the browser actually sent a file (an empty file input reports UPLOAD_ERR_NO_FILE). */
    private function hasFile(?array $file): bool
    {
        return $file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function reportingTeamId(User $user): int
    {
        if (!$user->isTeamPrincipal() || $user->getTeamId() === null) {
            throw new ForbiddenException('Only a team principal can file a protest');
        }

        return $user->getTeamId();
    }

    /**
     * Case file for the case page.
     *
     * @throws NotFoundException  when the case does not exist
     * @throws ForbiddenException when a team principal opens a case of another team
     */
    public function getForUser(User $user, int $id): IncidentDetail
    {
        $detail = $this->details->findById($id) ?? throw new NotFoundException('Incident not found');

        if (!$user->isSteward() && !$detail->belongsToTeam($user->getTeamId() ?? 0)) {
            throw new ForbiddenException('This case belongs to another team');
        }

        return $detail;
    }

    /**
     * View data for the case page (formatting only, no markup).
     *
     * @return array<string, mixed>
     */
    public function dossier(IncidentDetail $detail): array
    {
        $evidence = $detail->getEvidence();
        $penalty = $detail->getPenalty();

        return [
            'id' => $detail->getId(),
            'code' => $detail->getCode(),
            'title' => $detail->getArticleTitle(),
            'article' => $detail->getArticleReference(),
            'description' => $detail->getDescription(),
            'grandPrix' => $detail->getGrandPrixName(),
            'circuit' => $detail->getCircuitName(),
            'lap' => $detail->getLapNumber(),
            'totalLaps' => $detail->getTotalLaps(),
            'accused' => $detail->getAccused(),
            'affected' => $detail->getAffected(),
            'status' => $detail->getStatus()->value,
            'statusLabel' => $this->statusLabel($detail->getStatus()->value),
            'reportedAt' => $detail->getReportedAt()->setTimezone(new \DateTimeZone('UTC'))->format('d.m.Y, H:i') . ' UTC',
            'reporter' => $detail->getReporterName(),
            'reporterTeam' => $detail->getReporterTeam(),
            'evidence' => $evidence === null ? null : [
                'url' => $evidence->getFilePath(),
                'name' => $evidence->getOriginalName(),
                'size' => $this->formatSize($evidence->getSizeBytes()),
                'sha256' => $evidence->getSha256(),
            ],
            'penalty' => $penalty === null ? null : [
                'label' => $this->penaltyLabel($penalty['type'], $penalty['value']),
                'points' => $penalty['points'],
                'article' => $penalty['articleReference'],
                'reason' => $penalty['reason'],
            ],
        ];
    }

    /**
     * REPORTED -> UNDER_INVESTIGATION.
     *
     * @return array{id: int, code: string, status: string, statusLabel: string, url: string}
     * @throws NotFoundException            when the case does not exist
     * @throws UnprocessableEntityException when the transition is not allowed
     */
    public function investigate(int $id): array
    {
        return $this->transition($id, IncidentStatus::UNDER_INVESTIGATION);
    }

    /**
     * UNDER_INVESTIGATION -> NO_FURTHER_ACTION.
     *
     * @return array{id: int, code: string, status: string, statusLabel: string, url: string}
     * @throws NotFoundException            when the case does not exist
     * @throws UnprocessableEntityException when the transition is not allowed
     */
    public function dismiss(int $id): array
    {
        return $this->transition($id, IncidentStatus::NO_FURTHER_ACTION);
    }

    /** Status change under a row lock, so two stewards cannot move the same case at once. */
    private function transition(int $id, IncidentStatus $target): array
    {
        $incident = $this->transactions->transactional(function () use ($id, $target) {
            $incident = $this->incidentRepository->findByIdForUpdate($id) ?? throw new NotFoundException('Incident not found');
            $incident->getStatus()->assertCanTransitionTo($target);
            $this->incidentRepository->updateStatus($id, $target);

            return $incident;
        });

        return [
            'id' => $incident->getId(),
            'code' => sprintf('INC-%03d', $incident->getId()),
            'status' => $target->value,
            'statusLabel' => $this->statusLabel($target->value),
            'url' => '/incidents/' . $incident->getId(),
        ];
    }

    private function formatSize(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '') . ' MB'
            : number_format(max(1, (int) round($bytes / 1024)), 0, ',', '') . ' KB';
    }

    /**
     * @param string  $status one of IncidentCriteria::GROUPS (anything else means "all")
     * @param ?string $gp     Grand Prix id, "all", or null for the active Grand Prix
     */
    public function list(User $user, string $status, ?string $gp, string $search, int $page): IncidentListResult
    {
        $status = in_array($status, IncidentCriteria::GROUPS, true) ? $status : IncidentCriteria::GROUP_ALL;
        $search = mb_substr(trim($search), 0, 100);
        $active = $this->grandsPrix->findActive();
        $grandPrixId = $this->resolveGrandPrixId($gp, $active?->getId());

        $teamScope = $user->isSteward() ? null : ($user->getTeamId() ?? 0);
        $criteria = new IncidentCriteria($teamScope, $grandPrixId, $status, $search);

        $total = $this->incidents->count($criteria);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        $summaries = $this->incidents->findPage($criteria, self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        return new IncidentListResult(
            rows: array_map($this->toRow(...), $summaries),
            total: $total,
            page: $page,
            perPage: self::PER_PAGE,
            counts: $this->counters($criteria),
            statusGroup: $status,
            grandPrixId: $grandPrixId,
            search: $search,
            activeGrandPrix: $active,
            grandsPrix: $this->grandsPrix->findAll(),
        );
    }

    /**
     * IncidentRow shape shared with GET /api/incidents.
     *
     * @return array{id: int, code: string, lap: int, drivers: list<array{number: int, code: string, name: string}>, title: string, summary: string, status: string, statusLabel: string, penaltyLabel: ?string, url: string}
     */
    public function toRow(IncidentSummary $incident): array
    {
        return [
            'id' => $incident->getId(),
            'code' => $incident->getCode(),
            'lap' => $incident->getLapNumber(),
            'drivers' => $incident->getDrivers(),
            'title' => $incident->getArticleTitle(),
            'summary' => mb_strimwidth($incident->getDescription(), 0, self::SUMMARY_LENGTH, '…', 'UTF-8'),
            'status' => $incident->getStatus()->value,
            'statusLabel' => $this->statusLabel($incident->getStatus()->value),
            'penaltyLabel' => $this->penaltyLabel($incident->getPenaltyType(), $incident->getPenaltyValue()),
            'url' => '/incidents/' . $incident->getId(),
        ];
    }

    /** "gp" absent = active Grand Prix, "all" = no filter, a number = that Grand Prix (only if it exists). */
    private function resolveGrandPrixId(?string $gp, ?int $activeId): ?int
    {
        if ($gp === null || $gp === '') {
            return $activeId;
        }
        if ($gp === self::ALL_GRANDS_PRIX) {
            return null;
        }
        if (ctype_digit($gp) && $this->grandsPrix->findById((int) $gp) !== null) {
            return (int) $gp;
        }

        return $activeId;
    }

    /** @return array<string, int> */
    private function counters(IncidentCriteria $criteria): array
    {
        $counts = $this->incidents->countByStatus($criteria);
        $counts['CLOSED'] = $counts[IncidentStatus::PENALTY_ISSUED->value] + $counts[IncidentStatus::NO_FURTHER_ACTION->value];
        $counts['TOTAL'] = array_sum(array_intersect_key($counts, array_flip(array_map(static fn (IncidentStatus $status): string => $status->value, IncidentStatus::cases()))));

        return $counts;
    }

    private function statusLabel(string $status): string
    {
        return $this->translator->translate('status.' . $status);
    }

    private function penaltyLabel(?PenaltyType $type, ?int $value): ?string
    {
        return $type === null
            ? null
            : $this->translator->translate('penalty_label.' . $type->value, ['value' => $value ?? 0]);
    }
}
