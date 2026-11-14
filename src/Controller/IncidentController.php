<?php

declare(strict_types=1);

namespace App\Controller;

use App\Demo\DemoData;
use App\Exception\NotFoundException;
use App\Http\Request;
use App\Http\Response;

final class IncidentController extends AbstractController
{
    private const FILTERS = ['all', 'reported', 'investigation', 'closed'];

    public function __construct(private readonly DemoData $demo)
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $filter = $this->queryString($request, 'status', 'all');
        $filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
        $query = $this->queryString($request, 'q');
        $page = (int) $this->queryString($request, 'page', '1');

        return $this->render('incidents/index', $this->demo->shell('/incidents') + [
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

        return $this->render('incidents/show', $this->demo->shell('/incidents') + [
            'pageTitle' => $incident['code'] . ' — ApexSteward',
            'incident' => $incident,
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->isPost()) {
            return Response::redirect('/incidents');
        }

        return $this->render('incidents/create', $this->demo->shell('/incidents/create') + [
            'pageTitle' => 'Nowy protest — ApexSteward',
            'form' => $this->demo->reportForm(),
        ]);
    }
}
