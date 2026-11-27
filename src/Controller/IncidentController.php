<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\Model\IncidentCriteria;
use App\Model\User;
use App\Service\AuthService;
use App\Service\Command\ReportIncidentCommand;
use App\Service\IncidentService;
use App\Service\ShellService;
use App\Service\ValidationMessages;
use App\View\ViewHelper;

final class IncidentController extends AbstractController
{
    private const FORM_FIELDS = [
        'grand_prix_id', 'lap_number', 'accused_driver_id', 'affected_driver_id', 'infringement_article_id', 'description',
    ];

    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly AuthService $auth,
        private readonly IncidentService $incidentService,
        private readonly Session $session,
        private readonly ValidationMessages $messages,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();
        $gp = $request->getQuery('gp');
        $result = $this->incidentService->list(
            $user,
            $this->queryString($request, 'status', IncidentCriteria::GROUP_ALL),
            is_string($gp) ? $gp : null,
            $this->queryString($request, 'q'),
            (int) $this->queryString($request, 'page', '1'),
        );

        return $this->render('incidents/index', $this->shell->build('/incidents') + [
            'pageTitle' => $this->view->t('incidents.page_title'),
            'counts' => $result->getCounts(),
            'filter' => $result->getStatusGroup(),
            'query' => $result->getSearch(),
            'gpValue' => $result->getGrandPrixId() === null ? IncidentService::ALL_GRANDS_PRIX : (string) $result->getGrandPrixId(),
            'grandsPrix' => $result->getGrandsPrix(),
            'activeGrandPrix' => $result->getActiveGrandPrix(),
            'rows' => $result->getRows(),
            'total' => $result->getTotal(),
            'page' => $result->getPage(),
            'pages' => $result->getPages(),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        // 404 for a missing case, 403 for another team's case (ownership is checked on the server).
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();
        $incident = $this->incidentService->dossier($this->incidentService->getForUser($user, $id));

        return $this->render('incidents/show', $this->shell->build('/incidents') + [
            'pageTitle' => $incident['code'],
            'incident' => $incident,
            'isSteward' => $user->isSteward(),
        ]);
    }

    /** GET /incidents/create: empty protest form (role is checked by the route middleware). */
    public function create(Request $request): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();

        return $this->renderForm($user, [], []);
    }

    /** POST /incidents: files the protest, or shows the form again with field errors (422). */
    public function store(Request $request): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();
        $old = $this->oldInput($request);

        try {
            $incident = $this->incidentService->report($user, $this->command($request));
        } catch (ValidationException $e) {
            return $this->renderForm($user, $old, $this->messages->messages($e->getErrors()))->setStatusCode(422);
        }

        $this->session->addFlash('success', $this->view->t('flash.protest_filed', ['code' => $incident->getCode()]));

        return Response::redirect('/incidents/' . $incident->getId());
    }

    /**
     * @param array<string, string> $old    submitted values, shown again after an error
     * @param array<string, string> $errors field => message
     */
    private function renderForm(User $user, array $old, array $errors): Response
    {
        $form = $this->incidentService->reportForm($user);

        return $this->render('incidents/create', $this->shell->build('/incidents/create') + [
            'pageTitle' => $this->view->t('report.page_title'),
            'form' => $form,
            'old' => $old,
            'errors' => $errors,
        ]);
    }

    /** @return array<string, string> */
    private function oldInput(Request $request): array
    {
        $old = [];
        foreach (self::FORM_FIELDS as $field) {
            $value = $request->getParsedBody($field, '');
            $old[$field] = is_string($value) ? $value : '';
        }

        return $old;
    }

    private function command(Request $request): ReportIncidentCommand
    {
        $input = $this->oldInput($request);

        return new ReportIncidentCommand(
            $this->positiveInt($input['grand_prix_id']),
            $this->positiveInt($input['lap_number']),
            $this->positiveInt($input['accused_driver_id']),
            $input['affected_driver_id'] === '' ? null : $this->positiveInt($input['affected_driver_id']),
            $this->positiveInt($input['infringement_article_id']),
            trim($input['description']),
            $request->getFile('evidence'),
        );
    }
}
