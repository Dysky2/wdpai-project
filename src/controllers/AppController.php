<?php

declare(strict_types=1);

abstract class AppController
{
    protected function render(string $template, array $variables = []): void
    {
        $templatePath = dirname(__DIR__, 2) . '/public/views/' . $template . '.html.php';
        $layoutPath = dirname(__DIR__, 2) . '/public/views/layout.html.php';

        if (!is_file($templatePath) || !is_file($layoutPath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($variables, EXTR_SKIP);
        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}