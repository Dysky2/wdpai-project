<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\LicenceService;
use App\Service\ShellService;
use App\View\ViewHelper;

final class StandingsController extends AbstractController
{
    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly LicenceService $licences,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $filter = $this->queryString($request, 'filter', 'all');
        $query = $this->queryString($request, 'q');
        $table = $this->licences->table($filter, $query, (int) $this->queryString($request, 'page', '1'));

        return $this->render('standings/index', $this->shell->build('/standings') + [
            'pageTitle' => 'Klasyfikacja punktów karnych — ApexSteward',
            'filter' => in_array($filter, ['risk', 'clean'], true) ? $filter : 'all',
            'query' => $query,
            'perPage' => LicenceService::PER_PAGE,
        ] + $table);
    }
}
