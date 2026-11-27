<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Http\Request;
use App\Http\Response;
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
        // Same validation as the HTML log: an invalid status, gp or page gives 422 (perPage is fixed on the server).
        $result = $this->incidentService->list($user, $this->incidentListQuery($request));

        return $this->json([
            'data' => $result->getRows(),
            'meta' => ['page' => $result->getPage(), 'perPage' => $result->getPerPage(), 'total' => $result->getTotal()],
        ]);
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
