<?php

declare(strict_types=1);

namespace App\View;

/**
 * Ready-made pager links for partials/pagination.html.php, so the template only loops and prints.
 */
final class Pagination
{
    /**
     * @param string                $path   page path, e.g. "/incidents"
     * @param array<string, scalar> $params current query without "page" (kept on every link)
     */
    public function __construct(
        private readonly string $path,
        private readonly array $params,
        private readonly int $page,
        private readonly int $pages,
    ) {
    }

    /** False when everything fits on one page (the pager is then not rendered). */
    public function hasPages(): bool
    {
        return $this->pages > 1;
    }

    public function previousUrl(): ?string
    {
        return $this->page > 1 ? $this->url($this->page - 1) : null;
    }

    public function nextUrl(): ?string
    {
        return $this->page < $this->pages ? $this->url($this->page + 1) : null;
    }

    /** @return list<array{number: int, url: string, current: bool}> */
    public function links(): array
    {
        $links = [];
        for ($number = 1; $number <= $this->pages; $number++) {
            $links[] = ['number' => $number, 'url' => $this->url($number), 'current' => $number === $this->page];
        }

        return $links;
    }

    private function url(int $page): string
    {
        return $this->path . '?' . http_build_query($this->params + ['page' => $page]);
    }
}
