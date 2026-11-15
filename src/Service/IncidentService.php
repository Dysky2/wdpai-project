<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Model\IncidentCriteria;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\IncidentSummary;
use App\Model\PenaltyType;
use App\Model\User;
use App\Repository\GrandPrixRepositoryInterface;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentListRepositoryInterface;

/**
 * Incident log use cases. Ownership scope is decided here, never in the view:
 * a team principal sees cases where their team reported or is accused, a steward sees everything.
 */
final class IncidentService
{
    public const PER_PAGE = 6;

    /** Value of the "gp" query parameter that disables the Grand Prix filter. */
    public const ALL_GRANDS_PRIX = 'all';

    private const STATUS_LABELS = [
        'REPORTED' => 'Zgłoszony',
        'UNDER_INVESTIGATION' => 'W trakcie dochodzenia',
        'PENALTY_ISSUED' => 'Kara nałożona',
        'NO_FURTHER_ACTION' => 'Bez dalszych działań',
    ];

    private const SUMMARY_LENGTH = 110;

    public function __construct(
        private readonly IncidentListRepositoryInterface $incidents,
        private readonly GrandPrixRepositoryInterface $grandsPrix,
        private readonly IncidentDetailRepositoryInterface $details,
    ) {
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
            'statusLabel' => self::STATUS_LABELS[$detail->getStatus()->value],
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
            'statusLabel' => self::STATUS_LABELS[$incident->getStatus()->value],
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
        $counts['TOTAL'] = array_sum(array_intersect_key($counts, self::STATUS_LABELS));

        return $counts;
    }

    private function penaltyLabel(?PenaltyType $type, ?int $value): ?string
    {
        return match ($type) {
            null => null,
            PenaltyType::TIME_PENALTY => 'Kara +' . $value . ' s',
            PenaltyType::GRID_DROP => 'Cofnięcie o ' . $value . ' poz.',
            PenaltyType::DRIVE_THROUGH => 'Przejazd przez aleję serwisową',
            PenaltyType::REPRIMAND => 'Nagana',
            PenaltyType::DISQUALIFICATION => 'Dyskwalifikacja',
        };
    }
}
