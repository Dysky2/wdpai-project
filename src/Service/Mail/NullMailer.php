<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Model\Penalty;

/** Placeholder until PHPMailer is wired in (card S6-26). */
final class NullMailer implements MailerInterface
{
    public function sendRuling(Penalty $penalty): void
    {
    }
}
