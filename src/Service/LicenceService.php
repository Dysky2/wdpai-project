<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\ClockInterface;
use App\Core\Translator;
use App\Model\IncidentCriteria;
use App\Model\IncidentStatus;
use App\Model\LicenceStatus;
use App\Model\StandingRow;
use App\Repository\IncidentListRepositoryInterface;
use App\Repository\StandingRepositoryInterface;

/** Superlicence table: filtering is done by the repository, ranking, KPIs and paging here. */
final class LicenceService
{
    public const PER_PAGE = 10;

    public function __construct(
        private readonly StandingRepositoryInterface $standings,
        private readonly IncidentListRepositoryInterface $incidents,
        private readonly ClockInterface $clock,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param string $filter one of StandingRepositoryInterface::FILTER_* (anything else means "all")
     * @return array{kpis: array<string, mixed>, rows: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function table(string $filter, string $search, int $page): array
    {
        $filter = in_array($filter, [StandingRepositoryInterface::FILTER_RISK, StandingRepositoryInterface::FILTER_CLEAN], true)
            ? $filter
            : StandingRepositoryInterface::FILTER_ALL;
        $search = mb_substr(trim($search), 0, 100);

        // The position is the rank in the whole table, so it does not change while filtering.
        $everyone = $this->standings->findAll();
        $positions = [];
        foreach ($everyone as $index => $row) {
            $positions[$row->getDriverId()] = $index + 1;
        }

        $matching = $filter === StandingRepositoryInterface::FILTER_ALL && $search === ''
            ? $everyone
            : $this->standings->findAll($filter, $search);

        $total = count($matching);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        $now = $this->clock->now();

        return [
            'kpis' => $this->kpis($everyone),
            'rows' => array_map(
                fn (StandingRow $row): array => $this->toRow($row, $positions[$row->getDriverId()], $now),
                array_slice($matching, ($page - 1) * self::PER_PAGE, self::PER_PAGE)
            ),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    /**
     * @param list<StandingRow> $everyone ordered by points, most first
     * @return array<string, mixed>
     */
    private function kpis(array $everyone): array
    {
        $total = count($everyone);
        $clean = count(array_filter($everyone, static fn (StandingRow $r): bool => $r->getActivePoints() === 0));
        $top = $everyone[0] ?? null;
        $topPoints = $top?->getActivePoints() ?? 0;
        $counts = $this->incidents->countByStatus(new IncidentCriteria(null, null));

        return [
            'total' => $total,
            'top' => ['name' => $top?->getFullName() ?? '—', 'points' => $topPoints],
            'toBan' => max(0, LicenceStatus::POINTS_LIMIT - $topPoints),
            'pending' => $counts[IncidentStatus::UNDER_INVESTIGATION->value] ?? 0,
            'clean' => $clean,
            'cleanPercent' => $total === 0 ? 0 : (int) round($clean / $total * 100),
        ];
    }

    /** @return array<string, mixed> */
    private function toRow(StandingRow $row, int $position, \DateTimeImmutable $now): array
    {
        $status = $row->getLicenceStatus();
        $expiry = $row->getNextExpiration();

        return [
            'position' => $position,
            'code' => $row->getCode(),
            'number' => $row->getRaceNumber(),
            'name' => $row->getFullName(),
            'team' => $row->getTeamName(),
            'points' => $row->getActivePoints(),
            'percent' => $row->getProgressPercent(),
            // Bar colour and width step follow the licence thresholds (LicenceStatus is the only place with them).
            'progressTone' => match ($status) {
                LicenceStatus::Suspended, LicenceStatus::AtBanRisk => 'high',
                LicenceStatus::Caution => 'medium',
                LicenceStatus::Active, LicenceStatus::Clean => 'low',
            },
            'progressStep' => max(0, min(LicenceStatus::POINTS_LIMIT, $row->getActivePoints())),
            'expiry' => $expiry?->setTimezone($now->getTimezone())->format('d.m.Y'),
            'daysRemaining' => $expiry === null ? null : max(0, (int) $now->diff($expiry)->format('%a')),
            'status' => $status->value,
            'statusLabel' => $this->translator->translate('licence_status.' . $status->value),
        ];
    }
}
