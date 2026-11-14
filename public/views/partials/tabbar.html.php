<?php
/** Mobile bottom tab bar; same $navItems and $activeRoute as the header. */
$navItems ??= [];
$activeRoute ??= '';
?>
<?php if ($navItems !== []): ?>
    <nav class="tabbar" aria-label="Nawigacja mobilna">
        <ul class="tabbar__list">
            <?php foreach ($navItems as $item): ?>
                <li>
                    <a class="tabbar__link" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $activeRoute === $item['route'] ? ' aria-current="page"' : '' ?>>
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
