<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\NotFoundException;
use App\Exception\UnprocessableEntityException;
use App\Model\IncidentDetail;
use App\Model\IncidentStatus;
use App\Model\SanctionOption;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\InfringementArticleRepositoryInterface;
use App\Repository\PenaltyRepositoryInterface;

/** Prepares the adjudication form (steward only; the penalty itself is saved in S3-17). */
final class AdjudicationService
{
    public function __construct(
        private readonly IncidentDetailRepositoryInterface $details,
        private readonly IncidentRepositoryInterface $incidents,
        private readonly InfringementArticleRepositoryInterface $articles,
        private readonly PenaltyRepositoryInterface $penalties,
    ) {
    }

    /**
     * @throws NotFoundException            when the case does not exist
     * @throws UnprocessableEntityException when the case is not UNDER_INVESTIGATION
     */
    public function formData(int $incidentId): AdjudicationForm
    {
        $detail = $this->details->findById($incidentId) ?? throw new NotFoundException('Incident not found');
        $incident = $this->incidents->findById($incidentId) ?? throw new NotFoundException('Incident not found');

        if ($detail->getStatus() !== IncidentStatus::UNDER_INVESTIGATION) {
            throw new UnprocessableEntityException('Only a case under investigation can be adjudicated.');
        }

        $articles = $this->articles->findAll();
        $first = $articles[0] ?? null;
        $defaultSanction = $first === null ? null
            : SanctionOption::forPreset($first->getDefaultPenaltyType(), $first->getDefaultSanctionValue());
        $defaultPoints = $first?->getDefaultPoints() ?? 0;
        $current = $this->penalties->sumActivePoints($incident->getAccusedDriverId());

        return new AdjudicationForm(
            $detail,
            $articles,
            SanctionOption::all(),
            $defaultSanction ?? SanctionOption::all()[0],
            $defaultPoints,
            $current,
            $current + $defaultPoints,
        );
    }
}
