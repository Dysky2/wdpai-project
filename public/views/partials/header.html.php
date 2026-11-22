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
        <a class="brand" href="/" aria-label="<?= $view->te('app.brand_aria') ?>">
            <span class="brand__accent" aria-hidden="true"></span>
            <span class="brand__text">
                <strong><?= $view->te('app.brand_title') ?></strong>
                <small><?= $view->te('app.brand_tagline') ?></small>
            </span>
        </a>

        <?php if ($navItems !== []): ?>
            <nav class="main-nav" aria-label="<?= $view->te('nav.main_aria') ?>">
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
            <nav class="lang-switch" aria-label="<?= $view->te('nav.language_aria') ?>">
                <?php foreach (['pl', 'en'] as $code): ?>
                    <a class="lang-switch__link" href="/lang/<?= $code ?>" hreflang="<?= $code ?>" lang="<?= $code ?>"<?= $view->lang() === $code ? ' aria-current="true"' : '' ?>><?= strtoupper($code) ?></a>
                <?php endforeach; ?>
            </nav>
            <?php if ($currentUser !== null): ?>
                <span class="user-chip"><?= htmlspecialchars($currentUser['roleLabel'] . ' (' . $currentUser['name'] . ')', ENT_QUOTES, 'UTF-8') ?></span>
                <form action="/logout" method="post">
                    <?= $view->csrfField() ?>
                    <button class="link-button" type="submit"><?= $view->te('nav.logout') ?></button>
                </form>
                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($currentUser['initial'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <button class="theme-toggle" type="button" aria-label="<?= $view->te('theme.toggle_label') ?>" aria-pressed="false" title="<?= $view->te('theme.toggle_title') ?>">
                <i aria-hidden="true">☾</i>
            </button>
        </div>
    </div>
</header>
