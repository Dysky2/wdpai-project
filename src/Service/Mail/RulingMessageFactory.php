<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Model\Penalty;
use App\Model\PenaltyType;
use App\Repository\DriverRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\InfringementArticleRepositoryInterface;
use App\Repository\UserRepositoryInterface;

/** Resolves recipients (principals of the accused driver's team and of the reporting team) and ruling details. */
final class RulingMessageFactory
{
    public function __construct(
        private readonly IncidentRepositoryInterface $incidents,
        private readonly DriverRepositoryInterface $drivers,
        private readonly UserRepositoryInterface $users,
        private readonly InfringementArticleRepositoryInterface $articles,
    ) {
    }

    public function fromPenalty(Penalty $penalty): RulingMessage
    {
        $incident = $this->incidents->findById($penalty->getIncidentId())
            ?? throw new \RuntimeException('Incident of the penalty not found.');
        $driver = $this->drivers->findById($penalty->getDriverId())
            ?? throw new \RuntimeException('Driver of the penalty not found.');
        $article = $this->articles->findById($penalty->getArticleId());

        $teamIds = [$driver->getTeamId()];
        $reporter = $this->users->findById($incident->getReportingUserId());
        if ($reporter?->getTeamId() !== null) {
            $teamIds[] = $reporter->getTeamId();
        }

        $recipients = [];
        foreach (array_unique($teamIds) as $teamId) {
            foreach ($this->users->findTeamPrincipals($teamId) as $principal) {
                $recipients[strtolower($principal->getEmail())] = $principal->getEmail();
            }
        }
        if ($recipients === []) {
            throw new \RuntimeException('No recipients for the ruling e-mail.');
        }

        return new RulingMessage(
            array_values($recipients),
            $incident->getCode(),
            sprintf('%s (#%d)', $driver->getFullName(), $driver->getRaceNumber()),
            $article === null ? '-' : $article->getCode() . ' ' . $article->getTitle(),
            $this->sanctionLabel($penalty->getType(), $penalty->getSanctionValue()),
            $penalty->getPointsAdded(),
            $penalty->getReason(),
        );
    }

    private function sanctionLabel(PenaltyType $type, ?int $value): string
    {
        return match ($type) {
            PenaltyType::TIME_PENALTY => sprintf('+%d s time penalty', (int) $value),
            PenaltyType::GRID_DROP => sprintf('%d-place grid drop', (int) $value),
            PenaltyType::DRIVE_THROUGH => 'Drive-through penalty',
            PenaltyType::REPRIMAND => 'Reprimand',
            PenaltyType::DISQUALIFICATION => 'Disqualification',
        };
    }
}
