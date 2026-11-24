<?php

declare(strict_types=1);

/** Mobile bottom tab bar; same $navItems and $activeRoute as the header. */
$navItems ??= [];
$activeRoute ??= '';
?>
<?php if ($navItems !== []): ?>
    <nav class="tabbar" aria-label="<?= $view->te('nav.mobile_aria') ?>">
        <ul class="tabbar__list">
            <?php foreach ($navItems as $item): ?>
                <li>
                    <a class="tabbar__link" href="<?= $view->e($item['href']) ?>"<?= $activeRoute === $item['route'] ? ' aria-current="page"' : '' ?>>
                        <?= $view->e($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
