<?php
/**
 * Site header. Variables (all optional):
 *  - $navItems:    list of ['label' => string, 'href' => string, 'route' => string]
 *  - $currentUser: null or ['roleLabel' => string, 'name' => string, 'initial' => string]
 *  - $activeRoute: path of the current page, used to mark the active link
 */
$navItems ??= [];
$currentUser ??= null;
$activeRoute ??= '';
?>
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="/" aria-label="F1 Stewards, strona główna">
            <span class="brand__accent" aria-hidden="true"></span>
            <span class="brand__text">
                <strong>F1 STEWARDS</strong>
                <small>Incident Management Portal</small>
            </span>
        </a>

        <?php if ($navItems !== []): ?>
            <nav class="main-nav" aria-label="Główna nawigacja">
                <ul class="main-nav__list">
                    <?php foreach ($navItems as $item): ?>
                        <li>
                            <a class="main-nav__link" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $activeRoute === $item['route'] ? ' aria-current="page"' : '' ?>>
                                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <div class="site-header__tools">
            <?php if ($currentUser !== null): ?>
                <span class="user-chip"><?= htmlspecialchars($currentUser['roleLabel'] . ' (' . $currentUser['name'] . ')', ENT_QUOTES, 'UTF-8') ?></span>
                <form action="/logout" method="post">
                    <button class="link-button" type="submit">Wyloguj</button>
                </form>
                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($currentUser['initial'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <button class="theme-toggle" type="button" aria-label="Włącz ciemny motyw" aria-pressed="false" title="Zmień motyw">
                <i aria-hidden="true">☾</i>
            </button>
        </div>
    </div>
</header>
