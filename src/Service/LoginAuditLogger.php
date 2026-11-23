<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\LoggerInterface;

/**
 * Writes failed login attempts to the audit log (storage/logs/app.log). The password is never logged.
 */
final class LoginAuditLogger
{
    private const EMAIL_LOG_LENGTH = 100;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param string $reason short machine-readable cause, e.g. "invalid_credentials"
     */
    public function logFailure(string $email, string $ip, string $reason): void
    {
        $this->logger->info(sprintf(
            'Failed login: reason=%s email="%s" ip=%s',
            $reason,
            $this->sanitize($email, self::EMAIL_LOG_LENGTH),
            $this->sanitize($ip, 45),
        ));
    }

    /** Strips control characters (log injection) and shortens the value. */
    private function sanitize(string $value, int $maxLength): string
    {
        $clean = preg_replace('/[^\P{C}]+/u', ' ', $value) ?? '';
        if ($clean === '' && $value !== '') {
            $clean = '?';
        }
        $clean = str_replace('"', "'", trim($clean));

        return mb_substr($clean, 0, $maxLength);
    }
}
