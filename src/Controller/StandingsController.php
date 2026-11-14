<?php

declare(strict_types=1);

namespace App\Controller;

use App\Demo\DemoData;
use App\Http\Request;
use App\Http\Response;

final class StandingsController extends AbstractController
{
    private const FILTERS = ['all', 'risk', 'clean'];

    public function __construct(private readonly DemoData $demo)
    {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $filter = $this->queryString($request, 'filter', 'all');
        $filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
        $query = $this->queryString($request, 'q');
        $page = (int) $this->queryString($request, 'page', '1');

        return $this->render('standings/index', $this->demo->shell('/standings') + [
            'pageTitle' => 'Klasyfikacja punktów karnych — ApexSteward',
            'filter' => $filter,
            'query' => $query,
            'perPage' => DemoData::STANDINGS_PER_PAGE,
        ] + $this->demo->standings($filter, $query, $page));
    }
}
