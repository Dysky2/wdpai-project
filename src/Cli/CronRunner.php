<?php

declare(strict_types=1);

namespace App\Cli;

use App\Core\LoggerInterface;
use App\Service\PenaltyService;

/** Dispatches `php bin/cron.php <job>`; returns the process exit code and writes the audit log. */
final class CronRunner
{
    public const EXPIRE_PENALTIES = 'expire-penalties';

    public function __construct(
        private readonly PenaltyService $penalties,
        private readonly LoggerInterface $log,
    ) {
    }

    /** @param list<string> $argv */
    public function run(array $argv): int
    {
        $job = $argv[1] ?? '';

        if ($job !== self::EXPIRE_PENALTIES) {
            $this->log->error(sprintf('Unknown job "%s". Available: %s', $job, self::EXPIRE_PENALTIES));

            return 2;
        }

        try {
            $this->log->info($this->penalties->expireOutdatedPenalties()->describe());

            return 0;
        } catch (\Throwable $e) {
            $this->log->error(sprintf('%s failed: %s', self::EXPIRE_PENALTIES, $e->getMessage()));

            return 1;
        }
    }
}
