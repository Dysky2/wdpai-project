<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Response;
use App\Service\IncidentListQuery;
use App\View\ViewHelper;

abstract class AbstractController
{
    private string $viewsPath;
    private string $layoutPath;

    public function __construct(protected readonly ViewHelper $view)
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

        // Template scope: $view (ViewHelper) plus the controller data
        $view = $this->view;
        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        ob_start();
        require $this->layoutPath;
        $html = ob_get_clean();

        return new Response((string) $html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /** Reads a query-string value as a string (arrays and missing keys give $default). */
    protected function queryString(Request $request, string $key, string $default = ''): string
    {
        $value = $request->getQuery($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * Validated incident-log query, shared by the HTML log and the JSON API.
     *
     * @throws \App\Exception\UnprocessableEntityException for an invalid status, gp or page (HTTP 422)
     */
    protected function incidentListQuery(Request $request): IncidentListQuery
    {
        $query = $request->getQuery();

        return IncidentListQuery::fromArray(is_array($query) ? $query : []);
    }

    /**
     * Form id field as a positive integer; 0 when the value is not one
     * (services reject 0 like an unknown id, so the user gets a field error instead of a 500).
     */
    protected function positiveInt(string $value): int
    {
        return ctype_digit($value) && strlen($value) < 10 ? (int) $value : 0;
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
