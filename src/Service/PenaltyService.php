<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\ClockInterface;
use App\Core\LoggerInterface;
use App\Core\TransactionRunnerInterface;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Exception\UnprocessableEntityException;
use App\Exception\ValidationException;
use App\Model\IncidentStatus;
use App\Model\Penalty;
use App\Model\SanctionOption;
use App\Model\User;
use App\Repository\DriverRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\InfringementArticleRepositoryInterface;
use App\Repository\PenaltyRepositoryInterface;
use App\Service\Command\IssuePenaltyCommand;
use App\Service\Mail\MailerInterface;

/**
 * Adjudication: one transaction records the penalty, closes the case and updates the Superlicence
 * balance of the driver. The e-mail is sent after the commit and can never undo the ruling.
 */
final class PenaltyService
{
    /** Written reasoning length in UTF-8 characters after trimming (the form and the script read the same limits). */
    public const REASON_MIN = 20;
    public const REASON_MAX = 1000;

    public function __construct(
        private readonly IncidentRepositoryInterface $incidents,
        private readonly DriverRepositoryInterface $drivers,
        private readonly PenaltyRepositoryInterface $penalties,
        private readonly InfringementArticleRepositoryInterface $articles,
        private readonly TransactionRunnerInterface $transactions,
        private readonly PenaltyPolicy $policy,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @throws ForbiddenException           when the user is not a steward
     * @throws ValidationException          with per-field errors (HTTP 422)
     * @throws NotFoundException            when the case or the driver does not exist
     * @throws UnprocessableEntityException when the case is not UNDER_INVESTIGATION
     */
    public function issuePenalty(IssuePenaltyCommand $cmd, User $steward): PenaltyOutcome
    {
        if (!$steward->isSteward()) {
            throw new ForbiddenException('Only a steward can issue a penalty');
        }

        $sanction = $this->validate($cmd);

        [$incident, $penalty, $points, $suspended] = $this->transactions->transactional(
            function () use ($cmd, $steward, $sanction): array {
                $incident = $this->incidents->findByIdForUpdate($cmd->incidentId)
                    ?? throw new NotFoundException('Incident not found');
                $incident->getStatus()->assertCanTransitionTo(IncidentStatus::PENALTY_ISSUED);

                // Row lock on the driver: two simultaneous rulings cannot both read the old balance.
                $driver = $this->drivers->lockById($incident->getAccusedDriverId())
                    ?? throw new NotFoundException('Driver not found');

                $now = $this->clock->now();
                $penalty = $this->penalties->save(new Penalty(
                    null,
                    (int) $incident->getId(),
                    $driver->getId(),
                    $steward->getId(),
                    $cmd->articleId,
                    $sanction->getType(),
                    $sanction->getValue(),
                    $cmd->points,
                    $cmd->reason,
                    $now,
                    $this->policy->expiryFor($now),
                    false,
                ));
                $this->incidents->updateStatus((int) $incident->getId(), IncidentStatus::PENALTY_ISSUED);

                $points = $this->penalties->sumActivePoints($driver->getId());
                $suspended = $driver->isSuspended() || $this->policy->shouldSuspend($points);
                if ($suspended && !$driver->isSuspended()) {
                    $this->drivers->updateSuspension($driver->getId(), true);
                }

                return [$incident, $penalty, $points, $suspended];
            }
        );

        $mailSent = $cmd->notifyTeam && $this->notify($penalty);

        return new PenaltyOutcome($incident, $penalty, $points, $suspended, $mailSent);
    }

    /**
     * CRON: one transaction flags every penalty past its 365 days and re-evaluates the suspension of the
     * drivers it touched. Idempotent: a second run finds nothing to expire.
     */
    public function expireOutdatedPenalties(): ExpirationResult
    {
        return $this->transactions->transactional(function (): ExpirationResult {
            $expired = $this->penalties->expireOutdated($this->clock->now());

            $points = 0;
            $driverIds = [];
            foreach ($expired as $row) {
                $points += $row['points'];
                $driverIds[$row['driverId']] = true;
            }

            $reinstated = [];
            foreach (array_keys($driverIds) as $driverId) {
                $driver = $this->drivers->lockById($driverId);
                if ($driver === null || !$driver->isSuspended()) {
                    continue;
                }
                if (!$this->policy->shouldSuspend($this->penalties->sumActivePoints($driverId))) {
                    $this->drivers->updateSuspension($driverId, false);
                    $reinstated[] = $driver->getCode();
                }
            }

            return new ExpirationResult(count($expired), $points, $reinstated);
        });
    }

    /** A mail failure is logged and reported as "not sent"; the committed ruling stays. */
    private function notify(Penalty $penalty): bool
    {
        try {
            $this->mailer->sendRuling($penalty);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Ruling e-mail for penalty %d failed: %s', (int) $penalty->getId(), $e->getMessage()));

            return false;
        }
    }

    private function validate(IssuePenaltyCommand $cmd): SanctionOption
    {
        $errors = [];

        if ($cmd->articleId < 1 || $this->articles->findById($cmd->articleId) === null) {
            $errors['article_id'] = ['key' => 'validation.article_unknown', 'params' => []];
        }

        $sanction = SanctionOption::fromField($cmd->sanction);
        if ($sanction === null || !$sanction->isValid()) {
            $errors['sanction'] = ['key' => 'validation.sanction_unknown', 'params' => []];
        }

        if ($cmd->points < 0 || $cmd->points > PenaltyPolicy::MAX_POINTS_PER_PENALTY) {
            $errors['points'] = ['key' => 'validation.points_range', 'params' => ['max' => PenaltyPolicy::MAX_POINTS_PER_PENALTY]];
        }

        $reason = trim($cmd->reason);
        $length = mb_check_encoding($reason, 'UTF-8') ? mb_strlen($reason, 'UTF-8') : -1;
        if ($length < self::REASON_MIN || $length > self::REASON_MAX) {
            $errors['reason'] = [
                'key' => 'validation.reason_length',
                'params' => ['min' => self::REASON_MIN, 'max' => self::REASON_MAX],
            ];
        }

        if ($errors !== [] || $sanction === null) {
            throw new ValidationException($errors);
        }

        return $sanction;
    }
}
