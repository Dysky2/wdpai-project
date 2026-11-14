<div class="app-shell">
    <?php require __DIR__ . '/partials/navigation.html.php'; ?>

    <main class="dashboard-main not-found-main">
        <header class="topbar">
            <div>
                <p class="eyebrow">FIA / RACE CONTROL</p>
                <h1><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <span class="status-badge"><i></i>System online</span>
        </header>

        <section class="not-found-card" aria-labelledby="error-heading">
            <div class="not-found-visual" aria-hidden="true">
                <?php foreach (str_split((string) $status) as $index => $digit): ?>
                    <span<?= $index === 1 ? ' class="not-found-zero"' : '' ?>><?= htmlspecialchars($digit, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
            </div>
            <div class="not-found-content">
                <p class="eyebrow">KOMUNIKAT SYSTEMOWY / <?= (int) $status ?></p>
                <h2 id="error-heading"><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8') ?></p>
                <a class="not-found-button" href="/">Wróć na dashboard <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>
</div>
