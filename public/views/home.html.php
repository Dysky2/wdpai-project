<div class="app-shell">
    <?php require __DIR__ . '/partials/navigation.html.php'; ?>

    <main class="dashboard-main">
        <header class="topbar">
            <div>
                <p class="eyebrow">FIA / RACE CONTROL</p>
                <h1><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="topbar-actions">
                <button class="icon-button" type="button" aria-label="Powiadomienia">
                    <span aria-hidden="true">♧</span><i></i>
                </button>
                <div class="user-chip">
                    <span class="avatar avatar--small">MK</span>
                    <span><strong>M. Kowalska</strong><small>Przewodnicząca</small></span>
                </div>
            </div>
        </header>

            <section class="hero-card" aria-labelledby="race-heading">
                <div>
                    <p class="eyebrow eyebrow--light">NASTĘPNE POSIEDZENIE</p>
                    <h2 id="race-heading"><?= htmlspecialchars((string) $race['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="hero-meta"><?= htmlspecialchars((string) $race['round'], ENT_QUOTES, 'UTF-8') ?> <span>•</span> <?= htmlspecialchars((string) $race['date'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <span class="status-badge status-badge--light"><i></i><?= htmlspecialchars((string) $race['status'], ENT_QUOTES, 'UTF-8') ?></span>
            </section>

            <section class="stats-grid" aria-label="Podsumowanie weekendu wyścigowego">
                <?php foreach ($stats as $stat): ?>
                    <article class="stat-card">
                        <p><?= htmlspecialchars((string) $stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
                        <strong><?= htmlspecialchars((string) $stat['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars((string) $stat['trend'], ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                <?php endforeach; ?>
            </section>

            <div class="content-grid">
                <section class="panel panel--cases" aria-labelledby="cases-heading">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">PRIORYTETOWE ZGŁOSZENIA</p>
                            <h2 id="cases-heading">Sprawy do rozpatrzenia</h2>
                        </div>
                        <a class="text-link" href="/projects">Zobacz wszystkie <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="case-list">
                        <a class="case-row" href="/projects/1">
                            <span class="case-number">01</span>
                            <span class="case-info"><strong>Incydent na torze</strong><small>Verstappen / Norris · Zakręt 4</small></span>
                            <span class="priority priority--high">Wysoki</span>
                            <span class="case-arrow" aria-hidden="true">→</span>
                        </a>
                        <a class="case-row" href="/projects/2">
                            <span class="case-number">02</span>
                            <span class="case-info"><strong>Przekroczenie limitów toru</strong><small>Leclerc · Okrążenie 38</small></span>
                            <span class="priority priority--medium">Średni</span>
                            <span class="case-arrow" aria-hidden="true">→</span>
                        </a>
                        <a class="case-row" href="/projects/3">
                            <span class="case-number">03</span>
                            <span class="case-info"><strong>Niebezpieczne wypuszczenie</strong><small>Box Ferrari · Pit lane</small></span>
                            <span class="priority priority--low">Niski</span>
                            <span class="case-arrow" aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>

                <section class="panel" aria-labelledby="team-heading">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">ZESPÓŁ SĘDZIOWSKI</p>
                            <h2 id="team-heading">Dyżur dzisiaj</h2>
                        </div>
                        <span class="team-count"><?= count($judges) ?> osoby</span>
                    </div>
                    <div class="judge-list">
                        <?php foreach ($judges as $judge): ?>
                            <div class="judge-row">
                                <span class="avatar"><?= htmlspecialchars((string) $judge['initials'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="judge-info"><strong><?= htmlspecialchars((string) $judge['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) $judge['role'], ENT_QUOTES, 'UTF-8') ?></small></span>
                                <span class="online-status"><i></i><?= htmlspecialchars((string) $judge['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="outline-button" href="/about">Zarządzaj zespołem <span aria-hidden="true">↗</span></a>
                </section>
            </div>
    </main>
</div>
