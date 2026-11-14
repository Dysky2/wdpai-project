<?php

declare(strict_types=1);

namespace App\Model;

use App\Exception\UnprocessableEntityException;

/**
 * Case state machine: REPORTED -> UNDER_INVESTIGATION -> PENALTY_ISSUED | NO_FURTHER_ACTION.
 */
enum IncidentStatus: string
{
    case REPORTED = 'REPORTED';
    case UNDER_INVESTIGATION = 'UNDER_INVESTIGATION';
    case PENALTY_ISSUED = 'PENALTY_ISSUED';
    case NO_FURTHER_ACTION = 'NO_FURTHER_ACTION';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::REPORTED => [self::UNDER_INVESTIGATION],
            self::UNDER_INVESTIGATION => [self::PENALTY_ISSUED, self::NO_FURTHER_ACTION],
            self::PENALTY_ISSUED, self::NO_FURTHER_ACTION => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /** @throws UnprocessableEntityException when the transition is not allowed (HTTP 422) */
    public function assertCanTransitionTo(self $target): void
    {
        if (!$this->canTransitionTo($target)) {
            throw new UnprocessableEntityException(
                sprintf('Transition %s -> %s is not allowed.', $this->value, $target->value)
            );
        }
    }
}
