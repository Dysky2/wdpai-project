<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Exception\UnprocessableEntityException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Session;
use App\Service\AdjudicationService;
use App\Service\AuthService;
use App\Service\Command\IssuePenaltyCommand;
use App\Service\IncidentService;
use App\Service\PenaltyOutcome;
use App\Service\PenaltyService;
use App\Service\ShellService;
use App\Service\ValidationMessages;
use App\View\ViewHelper;

final class StewardController extends AbstractController
{
    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly AuthService $auth,
        private readonly AdjudicationService $adjudication,
        private readonly IncidentService $incidentService,
        private readonly PenaltyService $penalties,
        private readonly Session $session,
        private readonly ValidationMessages $messages,
    ) {
        parent::__construct($view);
    }

    public function adjudicate(Request $request, int $id): Response
    {
        $old = [];
        $errors = [];

        if ($request->isPost()) {
            $steward = $this->auth->currentUser() ?? throw new UnauthorizedException();
            $old = $this->oldInput($request);

            try {
                $outcome = $this->penalties->issuePenalty($this->command($request, $id, $old), $steward);
            } catch (ValidationException $e) {
                $errors = $this->messages->messages($e->getErrors());
                $outcome = null;
            }

            if ($outcome !== null) {
                $this->session->addFlash('success', $this->successMessage($outcome));

                return Response::redirect('/incidents/' . $id);
            }
        }

        // 404 for a missing case (role is checked by the route middleware).
        try {
            $form = $this->adjudication->formData($id);
        } catch (UnprocessableEntityException $e) {
            if ($request->isPost()) {
                throw $e;
            }

            // Opened by URL for a case that is not under investigation (new or already decided): explain instead of a bare 422 page.
            $this->session->addFlash('error', $this->view->t('flash.adjudicate_needs_investigation'));

            return Response::redirect('/incidents/' . $id);
        }
        $incident = $this->incidentService->dossier($form->getIncident());

        $response = $this->render('stewards/adjudicate', $this->shell->build('/incidents') + [
            'pageTitle' => $this->view->t('adjudicate.page_title', ['code' => $incident['code']]),
            'incident' => $incident,
            'articles' => $form->getArticles(),
            'sanctions' => $form->getSanctions(),
            'defaultSanction' => $old['sanction'] ?? $form->getDefaultSanction()->toField(),
            'defaultPoints' => isset($old['points']) ? (int) $old['points'] : $form->getDefaultPoints(),
            'currentPoints' => $form->getCurrentPoints(),
            'projectedPoints' => $form->getProjectedPoints(),
            'reasonMin' => PenaltyService::REASON_MIN,
            'reasonMax' => PenaltyService::REASON_MAX,
            'old' => $old,
            'errors' => $errors,
        ]);

        return $errors === [] ? $response : $response->setStatusCode(422);
    }

    /** @return array<string, string> */
    private function oldInput(Request $request): array
    {
        $old = [];
        foreach (['article_id', 'sanction', 'points', 'reason', 'notify_team'] as $field) {
            $value = $request->getParsedBody($field, '');
            $old[$field] = is_string($value) ? $value : '';
        }

        return $old;
    }

    /** @param array<string, string> $input */
    private function command(Request $request, int $incidentId, array $input): IssuePenaltyCommand
    {
        return new IssuePenaltyCommand(
            $incidentId,
            $this->positiveInt($input['article_id']),
            $input['sanction'],
            // A non-numeric value becomes -1 so that the service reports it as out of range.
            preg_match('/^\d{1,2}$/', $input['points']) === 1 ? (int) $input['points'] : -1,
            trim($input['reason']),
            $input['notify_team'] === '1',
        );
    }

    private function successMessage(PenaltyOutcome $outcome): string
    {
        $key = $outcome->suspended ? 'flash.ruling_saved_suspended' : 'flash.ruling_saved';

        return $this->view->t($key, [
            'code' => $outcome->incident->getCode(),
            'points' => $outcome->activePoints,
        ]);
    }
}
