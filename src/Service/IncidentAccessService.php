<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Model\Incident;
use App\Model\User;
use App\Repository\DriverRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\UserRepositoryInterface;

/** Resource ownership: who may see a given case. Stewards see all; a team principal only own team's cases. */
final class IncidentAccessService
{
    public function __construct(
        private readonly IncidentRepositoryInterface $incidents,
        private readonly UserRepositoryInterface $users,
        private readonly DriverRepositoryInterface $drivers,
    ) {
    }

    /** @throws NotFoundException when the case does not exist, ForbiddenException when it is not the user's */
    public function getForUser(User $user, int $incidentId): Incident
    {
        $incident = $this->incidents->findById($incidentId) ?? throw new NotFoundException('Incident not found');

        if (!$this->canView($user, $incident)) {
            throw new ForbiddenException('This case belongs to another team');
        }

        return $incident;
    }

    public function canView(User $user, Incident $incident): bool
    {
        if ($user->isSteward()) {
            return true;
        }

        $teamId = $user->getTeamId();
        if ($teamId === null) {
            return false;
        }

        $reporter = $this->users->findById($incident->getReportingUserId());
        if ($reporter !== null && $reporter->getTeamId() === $teamId) {
            return true;
        }

        $accused = $this->drivers->findById($incident->getAccusedDriverId());

        return $accused !== null && $accused->getTeamId() === $teamId;
    }
}
