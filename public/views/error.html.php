<section class="card panel-card error-card" aria-labelledby="error-heading">
    <div class="card__body">
        <p class="error-card__code" aria-hidden="true"><?= (int) $status ?></p>
        <p class="eyebrow">KOMUNIKAT SYSTEMOWY / <?= (int) $status ?></p>
        <h1 id="error-heading"><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="error-card__text"><?= htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8') ?></p>
        <a class="button" href="/">Wróć na stronę główną <span aria-hidden="true">→</span></a>
    </div>
</section>
