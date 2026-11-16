<?php
/**
 * Protest form. Data: $form (App\Service\ReportForm), $old (submitted values), $errors (field => message).
 */
$defaultGp = $form->getDefaultGrandPrix();
$selectedGp = $old['grand_prix_id'] ?? '';
if ($selectedGp === '' && $defaultGp !== null) {
    $selectedGp = (string) $defaultGp->getId();
}
$maxLap = $defaultGp?->getTotalLaps() ?? 0;
$team = $form->getTeam();
?>
<header class="page-header">
    <div>
        <p class="eyebrow">Protokół orzecznictwa FIA • ISC art. 13</p>
        <h1>Zgłoszenie incydentu / protest zespołu</h1>
        <p class="page-header__lead">Oficjalny portal dla szefów zespołów i dyrektorów sportowych do zgłaszania naruszeń na torze bezpośrednio do kierownictwa wyścigu i sędziów.</p>
    </div>
</header>

<section class="card form-card" aria-labelledby="report-heading">
    <header class="form-card__head">
        <div>
            <p class="eyebrow">Oficjalny dokument zgłoszeniowy</p>
            <h2 id="report-heading" class="card__title">Formularz zgłoszenia incydentu przez zespół</h2>
        </div>
    </header>

    <div class="notice" role="note">
        Protesty należy składać w ciągu 30 minut od zakończenia sesji lub zdarzenia (FIA ISC art. 13). Zgłoszenia po terminie są nieważne zgodnie z przepisami sportowymi.
    </div>

    <form class="form form-card__body" action="/incidents" method="post" enctype="multipart/form-data">
        <?= $view->csrfField() ?>
        <div class="form__row">
            <div class="form-field<?= isset($errors['grand_prix_id']) ? ' form-field--invalid' : '' ?>">
                <label for="grand_prix_id">Sesja Grand Prix <span class="required">*</span></label>
                <select id="grand_prix_id" name="grand_prix_id" required<?= isset($errors['grand_prix_id']) ? ' aria-invalid="true" aria-describedby="grand_prix_id-error"' : '' ?>>
                    <?php foreach ($form->getGrandsPrix() as $gp): ?>
                        <option value="<?= (int) $gp->getId() ?>" data-total-laps="<?= (int) $gp->getTotalLaps() ?>"<?= (string) $gp->getId() === $selectedGp ? ' selected' : '' ?>><?= $view->e($gp->getSeasonYear() . ' ' . $gp->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['grand_prix_id'])): ?>
                    <p class="form-field__error" id="grand_prix_id-error"><?= $view->e($errors['grand_prix_id']) ?></p>
                <?php endif; ?>
            </div>
            <div class="form-field<?= isset($errors['lap_number']) ? ' form-field--invalid' : '' ?>">
                <label for="lap_number">Numer okrążenia <span class="required">*</span></label>
                <input type="number" id="lap_number" name="lap_number" min="1" max="<?= (int) $maxLap ?>" value="<?= $view->e($old['lap_number'] ?? '') ?>" inputmode="numeric" required aria-describedby="lap-hint<?= isset($errors['lap_number']) ? ' lap_number-error' : '' ?>"<?= isset($errors['lap_number']) ? ' aria-invalid="true"' : '' ?>>
                <p class="form-field__hint" id="lap-hint">maks. <?= (int) $maxLap ?></p>
                <?php if (isset($errors['lap_number'])): ?>
                    <p class="form-field__error" id="lap_number-error"><?= $view->e($errors['lap_number']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form__row">
            <div class="form-field">
                <label for="team">Zespół składający zgłoszenie</label>
                <input type="text" id="team" value="<?= $view->e($team->getName()) ?>" readonly aria-describedby="team-hint">
                <p class="form-field__hint" id="team-hint">Kraj licencji: <?= $view->e($team->getLicenseCountry()) ?></p>
            </div>
            <div class="form-field<?= isset($errors['accused_driver_id']) ? ' form-field--invalid' : '' ?>">
                <label for="accused_driver_id">Oskarżony kierowca <span class="required">*</span></label>
                <select id="accused_driver_id" name="accused_driver_id" required<?= isset($errors['accused_driver_id']) ? ' aria-invalid="true" aria-describedby="accused_driver_id-error"' : '' ?>>
                    <option value="">— wybierz kierowcę —</option>
                    <?php foreach ($form->getAccusedDrivers() as $driver): ?>
                        <option value="<?= (int) $driver->getId() ?>"<?= (string) $driver->getId() === ($old['accused_driver_id'] ?? '') ? ' selected' : '' ?>>Bolid <?= (int) $driver->getRaceNumber() ?>: <?= $view->e($driver->getFullName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['accused_driver_id'])): ?>
                    <p class="form-field__error" id="accused_driver_id-error"><?= $view->e($errors['accused_driver_id']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form__row">
            <div class="form-field<?= isset($errors['affected_driver_id']) ? ' form-field--invalid' : '' ?>">
                <label for="affected_driver_id">Twój kierowca zaangażowany w zdarzenie</label>
                <select id="affected_driver_id" name="affected_driver_id"<?= isset($errors['affected_driver_id']) ? ' aria-invalid="true" aria-describedby="affected_driver_id-error"' : '' ?>>
                    <option value="">— brak —</option>
                    <?php foreach ($form->getOwnDrivers() as $driver): ?>
                        <option value="<?= (int) $driver->getId() ?>"<?= (string) $driver->getId() === ($old['affected_driver_id'] ?? '') ? ' selected' : '' ?>>Bolid <?= (int) $driver->getRaceNumber() ?>: <?= $view->e($driver->getFullName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['affected_driver_id'])): ?>
                    <p class="form-field__error" id="affected_driver_id-error"><?= $view->e($errors['affected_driver_id']) ?></p>
                <?php endif; ?>
            </div>
            <div class="form-field<?= isset($errors['infringement_article_id']) ? ' form-field--invalid' : '' ?>">
                <label for="infringement_article_id">Rodzaj zarzucanego naruszenia <span class="required">*</span></label>
                <select id="infringement_article_id" name="infringement_article_id" required<?= isset($errors['infringement_article_id']) ? ' aria-invalid="true" aria-describedby="infringement_article_id-error"' : '' ?>>
                    <option value="">— wybierz naruszenie —</option>
                    <?php foreach ($form->getArticles() as $article): ?>
                        <option value="<?= (int) $article->getId() ?>"<?= (string) $article->getId() === ($old['infringement_article_id'] ?? '') ? ' selected' : '' ?>><?= $view->e($article->getTitle() . ' (' . $article->getReference() . ')') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['infringement_article_id'])): ?>
                    <p class="form-field__error" id="infringement_article_id-error"><?= $view->e($errors['infringement_article_id']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-field<?= isset($errors['description']) ? ' form-field--invalid' : '' ?>">
            <label for="description">Opis zdarzenia (z perspektywy zespołu) <span class="required">*</span></label>
            <textarea id="description" name="description" rows="5" minlength="20" maxlength="500" required aria-describedby="description-hint<?= isset($errors['description']) ? ' description-error' : '' ?>"<?= isset($errors['description']) ? ' aria-invalid="true"' : '' ?>><?= $view->e($old['description'] ?? '') ?></textarea>
            <p class="form-field__hint" id="description-hint">20–500 znaków. Podaj sektor, prędkość na wierzchołku lub znaczniki czasu radia z telemetrii.</p>
            <?php if (isset($errors['description'])): ?>
                <p class="form-field__error" id="description-error"><?= $view->e($errors['description']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field<?= isset($errors['evidence']) ? ' form-field--invalid' : '' ?>">
            <label for="evidence">Dołącz zdjęcie / zrzut ekranu jako dowód</label>
            <label class="dropzone" for="evidence">
                <span class="dropzone__title">Wybierz plik obrazu (PNG, JPG do 5 MB)</span>
                <span class="dropzone__text">Przeciągnij klatkę z kamery, zrzut telemetrii lub zdjęcie ze ściany boksu</span>
            </label>
            <input class="visually-hidden-input" type="file" id="evidence" name="evidence" accept="image/png,image/jpeg"<?= isset($errors['evidence']) ? ' aria-invalid="true" aria-describedby="evidence-error"' : '' ?>>
            <?php if (isset($errors['evidence'])): ?>
                <p class="form-field__error" id="evidence-error"><?= $view->e($errors['evidence']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="representative">Upoważniony przedstawiciel zespołu</label>
            <input type="text" id="representative" value="<?= $view->e($form->getRepresentative()) ?>" readonly aria-describedby="representative-hint">
            <p class="form-field__hint" id="representative-hint">Składając zgłoszenie, potwierdzasz, że protest jest zgłaszany w dobrej wierze zgodnie z Międzynarodowym Kodeksem Sportowym FIA.</p>
        </div>

        <div class="form-actions form-actions--end">
            <button class="button" type="submit">Wyślij oficjalny protest do sędziów <span aria-hidden="true">→</span></button>
        </div>
    </form>
</section>
