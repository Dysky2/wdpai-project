<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\AdjudicationService;
use App\Service\IncidentService;
use App\Service\ShellService;
use App\View\ViewHelper;

final class StewardController extends AbstractController
{
    public function __construct(
        ViewHelper $view,
        private readonly ShellService $shell,
        private readonly AdjudicationService $adjudication,
        private readonly IncidentService $incidentService,
    ) {
        parent::__construct($view);
    }

    public function adjudicate(Request $request, int $id): Response
    {
        // 404 for a missing case, 422 unless the case is UNDER_INVESTIGATION (role is checked by the route middleware).
        $form = $this->adjudication->formData($id);

        if ($request->isPost()) {
            // Saving the penalty (transaction) is added in S3-17.
            return Response::redirect('/incidents/' . $id);
        }

        $incident = $this->incidentService->dossier($form->getIncident());

        return $this->render('stewards/adjudicate', $this->shell->build('/incidents') + [
            'pageTitle' => 'Orzeczenie ' . $incident['code'] . ' — ApexSteward',
            'incident' => $incident,
            'articles' => $form->getArticles(),
            'sanctions' => $form->getSanctions(),
            'defaultSanction' => $form->getDefaultSanction()->toField(),
            'defaultPoints' => $form->getDefaultPoints(),
            'currentPoints' => $form->getCurrentPoints(),
            'projectedPoints' => $form->getProjectedPoints(),
        ]);
    }
}
