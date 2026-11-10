<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\JsonResponse;
use App\Http\Response;

abstract class AbstractController
{
    private string $viewsPath;
    private string $layoutPath;

    public function __construct()
    {
        $this->viewsPath = dirname(__DIR__, 2) . '/public/views';
        $this->layoutPath = $this->viewsPath . '/layout.html.php';
    }

    /**
     * Renders a view template wrapped in the layout.
     * Uses output buffering so the template content is captured,
     * then injected as $content into the layout.
     *
     * @param string               $template  Template name without extension (e.g. 'home')
     * @param array<string, mixed> $data      Variables to extract into the template scope
     */
    protected function render(string $template, array $data = []): Response
    {
        $templatePath = $this->viewsPath . '/' . $template . '.html.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        if (!is_file($this->layoutPath)) {
            throw new \RuntimeException('Layout not found: ' . $this->layoutPath);
        }

        // Extract data into local scope (available inside the template)
        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        ob_start();
        require $this->layoutPath;
        $html = ob_get_clean();

        return new Response((string) $html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Returns a JSON response.
     *
     * @param mixed $data   Data to encode as JSON
     * @param int   $status HTTP status code
     */
    protected function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
