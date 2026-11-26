<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Exception\UnprocessableEntityException;
use App\Http\Request;
use App\Http\Response;
use App\Model\IncidentCriteria;
use App\Service\AuthService;
use App\Service\IncidentService;
use App\View\ViewHelper;

/** JSON API for the incident log (same scope and query as /incidents) and steward status actions. */
final class ApiIncidentController extends AbstractController
{
    public function __construct(
        ViewHelper $view,
        private readonly AuthService $auth,
        private readonly IncidentService $incidentService,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();
        $this->assertValidListQuery($request);
        $gp = $request->getQuery('gp');
        $result = $this->incidentService->list(
            $user,
            $this->queryString($request, 'status', IncidentCriteria::GROUP_ALL),
            is_string($gp) ? $gp : null,
            $this->queryString($request, 'q'),
            (int) $this->queryString($request, 'page', '1'),
        );

        return $this->json([
            'data' => $result->getRows(),
            'meta' => ['page' => $result->getPage(), 'perPage' => $result->getPerPage(), 'total' => $result->getTotal()],
        ]);
    }

    /**
     * The JSON contract rejects malformed list parameters with 422 instead of silently ignoring them.
     * `perPage` is fixed on the server and is not a parameter; a `page` past the last one is clamped.
     */
    private function assertValidListQuery(Request $request): void
    {
        $status = $request->getQuery('status');
        $gp = $request->getQuery('gp');
        $page = $request->getQuery('page');

        $invalid = match (true) {
            $status !== null && (!is_string($status) || !in_array($status, IncidentCriteria::GROUPS, true)) => 'status',
            $gp !== null && (!is_string($gp) || ($gp !== 'all' && !ctype_digit($gp))) => 'gp',
            $page !== null && (!is_string($page) || !ctype_digit($page) || (int) $page < 1) => 'page',
            default => null,
        };

        if ($invalid !== null) {
            throw new UnprocessableEntityException($this->view->t('validation.api_query', ['param' => $invalid]));
        }
    }

    /** REPORTED -> UNDER_INVESTIGATION (steward only, enforced by the route middleware). */
    public function investigate(Request $request, int $id): Response
    {
        return $this->json(['data' => $this->incidentService->investigate($id)]);
    }

    /** UNDER_INVESTIGATION -> NO_FURTHER_ACTION (steward only, enforced by the route middleware). */
    public function dismiss(Request $request, int $id): Response
    {
        return $this->json(['data' => $this->incidentService->dismiss($id)]);
    }
}
