<?php

declare(strict_types=1);

namespace App\Service\Mail;

use App\View\ViewHelper;

/** Renders the HTML and plain-text bodies from public/views/emails (templates see only $message and $view). */
final class RulingTemplateRenderer
{
    public function __construct(private readonly ViewHelper $view, private readonly string $templateDir)
    {
    }

    public function html(RulingMessage $message): string
    {
        return $this->render('penalty_decision.html.php', $message);
    }

    public function text(RulingMessage $message): string
    {
        return $this->render('penalty_decision.txt.php', $message);
    }

    private function render(string $file, RulingMessage $message): string
    {
        $view = $this->view;
        ob_start();
        try {
            include $this->templateDir . '/' . $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
