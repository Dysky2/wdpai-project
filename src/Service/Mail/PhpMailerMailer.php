<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\Model\Penalty;
use PHPMailer\PHPMailer\PHPMailer;

/** Sends the official ruling over SMTP (Mailpit locally). Throws on failure; the caller logs it. */
final class PhpMailerMailer implements MailerInterface
{
    public function __construct(
        private readonly MailSettings $settings,
        private readonly RulingMessageFactory $messages,
        private readonly RulingTemplateRenderer $renderer,
    ) {
    }

    public function sendRuling(Penalty $penalty): void
    {
        $message = $this->messages->fromPenalty($penalty);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->settings->host;
        $mail->Port = $this->settings->port;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Timeout = 10;
        $mail->SMTPAuth = $this->settings->requiresAuth();
        if ($this->settings->requiresAuth()) {
            $mail->Username = $this->settings->username;
            $mail->Password = $this->settings->password;
        }
        $mail->SMTPSecure = match ($this->settings->encryption) {
            'tls', 'starttls' => PHPMailer::ENCRYPTION_STARTTLS,
            'ssl', 'smtps' => PHPMailer::ENCRYPTION_SMTPS,
            default => '',
        };
        $mail->SMTPAutoTLS = $mail->SMTPSecure !== '';

        $mail->setFrom($this->settings->from, $this->settings->fromName);
        foreach ($message->recipients as $recipient) {
            $mail->addAddress($recipient);
        }

        $mail->isHTML(true);
        $mail->Subject = sprintf('FIA Stewards Decision - %s', $message->incidentCode);
        $mail->Body = $this->renderer->html($message);
        $mail->AltBody = $this->renderer->text($message);

        $mail->send();
    }
}
