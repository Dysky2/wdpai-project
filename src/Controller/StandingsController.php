<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Model\LicenceStatus;
use App\Repository\StandingRepositoryInterface;
use App\Service\LicenceService;
use App\Service\ShellService;
use App\View\Pagination;
use App\View\ViewHelper;

final class StandingsController extends AbstractController
{
    private const FILTER_CHIPS = [
        StandingRepositoryInterface::FILTER_ALL => 'standings.chip_all',
        StandingRepositoryInterface::FILTER_RISK => 'standings.chip_risk',
        StandingRepositoryInterface::FILTER_CLEAN => 'standings.chip_clean',
    ];

    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly LicenceService $licences,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $filter = $this->queryString($request, 'filter', StandingRepositoryInterface::FILTER_ALL);
        $filter = isset(self::FILTER_CHIPS[$filter]) ? $filter : StandingRepositoryInterface::FILTER_ALL;
        $query = $this->queryString($request, 'q');
        $table = $this->licences->table($filter, $query, (int) $this->queryString($request, 'page', '1'));

        return $this->render('standings/index', $this->shell->build('/standings') + [
            'pageTitle' => $this->view->t('standings.page_title'),
            'kpis' => $this->kpiCards($table['kpis']),
            'chips' => $this->filterChips($filter, $query),
            'filter' => $filter,
            'query' => $query,
            'rows' => $table['rows'],
            'shown' => count($table['rows']),
            'total' => $table['total'],
            'pointsLimit' => LicenceStatus::POINTS_LIMIT,
            'pager' => new Pagination('/standings', ['filter' => $filter, 'q' => $query], $table['page'], $table['pages']),
        ]);
    }

    /**
     * @param array<string, mixed> $kpis figures from LicenceService::table()
     * @return list<array<string, mixed>> cards for partials/kpi-card.html.php
     */
    private function kpiCards(array $kpis): array
    {
        return [
            [
                'label' => $this->view->t('standings.kpi_tracked'),
                'value' => $this->view->t('standings.kpi_drivers', ['count' => $kpis['total']]),
                'caption' => $this->view->t('standings.kpi_tracked_caption'),
                'icon' => '☰',
            ],
            [
                'label' => $this->view->t('standings.kpi_critical'),
                'value' => $kpis['top']['name'],
                'caption' => $this->view->t('standings.kpi_critical_caption', ['points' => $kpis['top']['points'], 'left' => $kpis['toBan']]),
                'icon' => '⚠',
                'tone' => 'accent',
            ],
            [
                'label' => $this->view->t('standings.kpi_pending'),
                'value' => $this->view->t('standings.kpi_cases', ['count' => $kpis['pending']]),
                'caption' => $this->view->t('standings.kpi_pending_caption'),
                'icon' => '⚑',
                'tone' => 'warning',
            ],
            [
                'label' => $this->view->t('standings.kpi_clean'),
                'value' => $this->view->t('standings.kpi_drivers', ['count' => $kpis['clean']]),
                'caption' => $this->view->t('standings.kpi_clean_caption', ['percent' => $kpis['cleanPercent']]),
                'icon' => '✔',
            ],
        ];
    }

    /** @return list<array{label: string, href: string, active: bool}> */
    private function filterChips(string $filter, string $query): array
    {
        $chips = [];
        foreach (self::FILTER_CHIPS as $key => $labelKey) {
            $chips[] = [
                'label' => $this->view->t($labelKey),
                'href' => '/standings?' . http_build_query(['filter' => $key, 'q' => $query]),
                'active' => $filter === $key,
            ];
        }

        return $chips;
    }
}
