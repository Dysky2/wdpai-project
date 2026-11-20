<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Model\Penalty;
use App\Service\Mail\MailerInterface;

final class FakeMailer implements MailerInterface
{
    /** @var list<Penalty> */
    public array $sent = [];

    public function __construct(private readonly bool $fails = false)
    {
    }

    public function sendRuling(Penalty $penalty): void
    {
        if ($this->fails) {
            throw new \RuntimeException('SMTP down');
        }
        $this->sent[] = $penalty;
    }
}
