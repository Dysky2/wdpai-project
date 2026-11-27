<?php declare(strict_types=1) ?>
<nav class="breadcrumb" aria-label="<?= $view->te('show.breadcrumb_aria') ?>">
    <a href="/incidents"><?= $view->te('nav.incidents') ?></a>
    <span aria-hidden="true">/</span>
    <a href="/incidents/<?= (int) $incident['id'] ?>"><?= $view->e($incident['code']) ?></a>
    <span aria-hidden="true">/</span>
    <span aria-current="page"><?= $view->te('adjudicate.breadcrumb') ?></span>
</nav>

<section class="card form-card" aria-labelledby="adjudicate-heading">
    <header class="form-card__head">
        <div>
            <h1 id="adjudicate-heading" class="card__title"><?= $view->te('adjudicate.heading') ?></h1>
            <p class="text-muted"><?= $view->te('adjudicate.lead', ['code' => $incident['code'], 'title' => $incident['title']]) ?></p>
        </div>
    </header>

    <form class="form form-card__body" action="/stewards/adjudicate/<?= (int) $incident['id'] ?>" method="post">
        <?= $view->csrfField() ?>
        <div class="form__row">
            <div class="form-field">
                <label for="incident-code"><?= $view->te('incidents.col_id') ?></label>
                <input type="text" id="incident-code" value="<?= $view->te('adjudicate.code_lap', ['code' => $incident['code'], 'lap' => (int) $incident['lap']]) ?>" readonly>
            </div>
            <div class="form-field">
                <label for="driver"><?= $view->te('adjudicate.penalised_driver') ?></label>
                <input type="text" id="driver" value="<?= $view->te('driver.car', ['number' => (int) $incident['accused']['number']]) ?> — <?= $view->e($incident['accused']['name']) ?> (<?= $view->e($incident['accused']['team']) ?>)" readonly>
            </div>
        </div>

        <div class="form-field">
            <label for="article_id"><?= $view->te('adjudicate.article') ?> <span class="required"><?= $view->te('adjudicate.required') ?></span></label>
            <select id="article_id" name="article_id" required>
                <?php foreach ($articles as $article): ?>
                    <option value="<?= (int) $article['id'] ?>"<?= (string) $article['id'] === $selectedArticle ? ' selected' : '' ?>
                            data-sanction="<?= $view->e($article['sanction']) ?>"
                            data-points="<?= (int) $article['points'] ?>"
                            data-wording="<?= $view->e($article['wording']) ?>"><?= $view->e($article['reference']) ?>: <?= $view->e($article['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['article_id'])): ?>
                <p class="form-field__error"><?= $view->e($errors['article_id']) ?></p>
            <?php endif; ?>
            <p class="form-field__hint"><?= $view->te('adjudicate.article_hint') ?></p>
        </div>

        <fieldset class="fieldset">
            <legend><?= $view->te('adjudicate.sanction') ?> <span class="required"><?= $view->te('adjudicate.choose_one') ?></span></legend>
            <div class="radio-cards">
                <?php foreach ($sanctions as $sanction): ?>
                    <label class="radio-card">
                        <input type="radio" name="sanction" value="<?= $view->e($sanction->toField()) ?>"<?= $sanction->toField() === $defaultSanction ? ' checked' : '' ?>>
                        <span><strong><?= $view->te($sanction->getTitleKey()) ?></strong><small><?= $view->te($sanction->getTextKey()) ?></small></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if (isset($errors['sanction'])): ?>
                <p class="form-field__error"><?= $view->e($errors['sanction']) ?></p>
            <?php endif; ?>
        </fieldset>

        <div class="form-field">
            <label for="points"><?= $view->te('adjudicate.points') ?></label>
            <div class="points-row">
                <input type="number" id="points" name="points" min="0" max="3" value="<?= (int) $defaultPoints ?>" inputmode="numeric" data-current-points="<?= (int) $currentPoints ?>">
                <p class="text-muted"><?= $view->te('adjudicate.points_hint') ?><br>
                    <span class="accent-text"><?= $view->te('adjudicate.balance_current', ['points' => (int) $currentPoints]) ?> <span data-projected-points><?= (int) $projectedPoints ?></span> <?= $view->te('adjudicate.points_unit') ?></span></p>
            </div>
            <?php if (isset($errors['points'])): ?>
                <p class="form-field__error"><?= $view->e($errors['points']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field<?= isset($errors['reason']) ? ' form-field--invalid' : '' ?>">
            <label for="reason"><?= $view->te('adjudicate.reason') ?></label>
            <textarea id="reason" name="reason" rows="6" minlength="<?= (int) $reasonMin ?>" maxlength="<?= (int) $reasonMax ?>" required<?= isset($errors['reason']) ? ' aria-invalid="true" aria-describedby="reason-error"' : '' ?>><?= $view->e($old['reason'] ?? '') ?></textarea>
            <?php if (isset($errors['reason'])): ?>
                <p class="form-field__error" id="reason-error"><?= $view->e($errors['reason']) ?></p>
            <?php endif; ?>
            <button class="button button--secondary" type="button" data-insert-precedent hidden><?= $view->te('adjudicate.insert_precedent') ?></button>
        </div>

        <label class="check-card">
            <input type="checkbox" name="notify_team" value="1" checked>
            <span><strong><?= $view->te('adjudicate.notify', ['accused' => $incident['accused']['team'], 'reporter' => $incident['reporterTeam']]) ?></strong>
                <small><?= $view->te('adjudicate.notify_hint') ?></small></span>
        </label>

        <div class="form-actions">
            <a class="button button--secondary" href="/incidents/<?= (int) $incident['id'] ?>">← <?= $view->te('adjudicate.cancel') ?></a>
            <button class="button" type="submit"><?= $view->te('adjudicate.submit') ?></button>
        </div>
    </form>
</section>
