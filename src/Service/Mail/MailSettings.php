<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Core\Config;

/** SMTP settings read from the environment (Mailpit locally: no authentication, no encryption). */
final readonly class MailSettings
{
    public function __construct(
        public string $host,
        public int $port,
        public string $username,
        public string $password,
        public string $encryption,
        public string $from,
        public string $fromName,
    ) {
    }

    public static function fromConfig(Config $config): self
    {
        return new self(
            $config->get('SMTP_HOST', 'mailpit') ?? 'mailpit',
            (int) ($config->get('SMTP_PORT', '1025') ?? '1025'),
            $config->get('SMTP_USER', '') ?? '',
            $config->get('SMTP_PASS', '') ?? '',
            strtolower($config->get('SMTP_ENCRYPTION', '') ?? ''),
            $config->get('SMTP_FROM', 'stewards@apexsteward.local') ?? 'stewards@apexsteward.local',
            $config->get('APP_NAME', 'ApexSteward') ?? 'ApexSteward',
        );
    }

    public function requiresAuth(): bool
    {
        return $this->username !== '';
    }
}
