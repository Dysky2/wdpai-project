<?php

declare(strict_types=1);

namespace App\Controller;

use App\Demo\DemoData;
use App\Exception\NotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Service\ShellService;
use App\View\ViewHelper;

final class IncidentController extends AbstractController
{
    private const FILTERS = ['all', 'reported', 'investigation', 'closed'];

    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly DemoData $demo,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $filter = $this->queryString($request, 'status', 'all');
        $filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
        $query = $this->queryString($request, 'q');
        $page = (int) $this->queryString($request, 'page', '1');

        return $this->render('incidents/index', $this->shell->build('/incidents') + [
            'pageTitle' => 'Dziennik incydentów — ApexSteward',
            'counts' => $this->demo->incidentCounts(),
            'filter' => $filter,
            'query' => $query,
            'perPage' => DemoData::PER_PAGE,
            'totalLaps' => DemoData::TOTAL_LAPS,
        ] + $this->demo->filterIncidents($filter, $query, $page));
    }

    public function show(Request $request, int $id): Response
    {
        $incident = $this->demo->incident($id) ?? throw new NotFoundException('Incident not found');

        return $this->render('incidents/show', $this->shell->build('/incidents') + [
            'pageTitle' => $incident['code'] . ' — ApexSteward',
            'incident' => $incident,
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/incidents');
        }

        return $this->render('incidents/create', $this->shell->build('/incidents/create') + [
            'pageTitle' => 'Nowy protest — ApexSteward',
            'form' => $this->demo->reportForm(),
        ]);
    }
}
