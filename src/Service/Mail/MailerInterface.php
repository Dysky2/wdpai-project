<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Model\Penalty;

interface MailerInterface
{
    /** Sends the official ruling to the teams involved (recipients are resolved by the implementation). */
    public function sendRuling(Penalty $penalty): void;
}
