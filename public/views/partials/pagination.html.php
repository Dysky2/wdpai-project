<?php
/** Pagination. Set $pager = ['path' => '/incidents', 'params' => [...query without page], 'page' => 1, 'pages' => 2] before including. */
$pagerUrl = static fn (int $page): string => $pager['path'] . '?' . http_build_query($pager['params'] + ['page' => $page]);
?>
<?php if ($pager['pages'] > 1): ?>
    <nav class="pagination" aria-label="Paginacja">
        <?php if ($pager['page'] > 1): ?>
            <a class="pagination__link" href="<?= htmlspecialchars($pagerUrl($pager['page'] - 1), ENT_QUOTES, 'UTF-8') ?>">Poprzednia</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
            <a class="pagination__link<?= $i === $pager['page'] ? ' pagination__link--current' : '' ?>" href="<?= htmlspecialchars($pagerUrl($i), ENT_QUOTES, 'UTF-8') ?>"<?= $i === $pager['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($pager['page'] < $pager['pages']): ?>
            <a class="pagination__link" href="<?= htmlspecialchars($pagerUrl($pager['page'] + 1), ENT_QUOTES, 'UTF-8') ?>">Następna</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
