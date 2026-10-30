<?php

declare(strict_types=1);

$activeRoute ??= '/';
?>
<aside class="sidebar">
    <a class="brand" href="/" aria-label="F1 Stewards, strona główna">
        <span class="brand-mark">F1</span>
        <span>
            <strong>STEWARDS</strong>
            <small>CONTROL ROOM</small>
        </span>
    </a>

    <nav class="main-nav" aria-label="Główna nawigacja">
        <a class="nav-link<?= $activeRoute === '/' ? ' nav-link--active' : '' ?>" href="/"<?= $activeRoute === '/' ? ' aria-current="page"' : '' ?>>
            <span aria-hidden="true">▦</span> Dashboard
        </a>
        <a class="nav-link<?= $activeRoute === '/projects' ? ' nav-link--active' : '' ?>" href="/projects"<?= $activeRoute === '/projects' ? ' aria-current="page"' : '' ?>>
            <span aria-hidden="true">◈</span> Sprawy
        </a>
        <a class="nav-link<?= $activeRoute === '/about' ? ' nav-link--active' : '' ?>" href="/about"<?= $activeRoute === '/about' ? ' aria-current="page"' : '' ?>>
            <span aria-hidden="true">ⓘ</span> O panelu
        </a>
    </nav>

    <div class="sidebar-footer">
        <span class="live-dot" aria-hidden="true"></span>
        <span><strong>System online</strong><small>Synchronizacja aktywna</small></span>
    </div>
</aside>
