<?php

declare(strict_types=1);

/** Pagination. Set $pager (App\View\Pagination, built by the controller) before including. */
?>
<?php if ($pager->hasPages()): ?>
    <nav class="pagination" aria-label="<?= $view->te('pagination.aria') ?>">
        <?php if ($pager->previousUrl() !== null): ?>
            <a class="pagination__link" href="<?= $view->e($pager->previousUrl()) ?>"><?= $view->te('pagination.prev') ?></a>
        <?php endif; ?>
        <?php foreach ($pager->links() as $link): ?>
            <a class="pagination__link<?= $link['current'] ? ' pagination__link--current' : '' ?>" href="<?= $view->e($link['url']) ?>"<?= $link['current'] ? ' aria-current="page"' : '' ?>><?= (int) $link['number'] ?></a>
        <?php endforeach; ?>
        <?php if ($pager->nextUrl() !== null): ?>
            <a class="pagination__link" href="<?= $view->e($pager->nextUrl()) ?>"><?= $view->te('pagination.next') ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
