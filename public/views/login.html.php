<section class="card panel-card" aria-labelledby="login-heading">
    <div class="card__body">
        <div class="panel-card__header">
            <p class="eyebrow"><?= $view->e($view->t('login.eyebrow')) ?></p>
            <h1 id="login-heading"><?= $view->e($view->t('login.heading')) ?></h1>
            <p class="panel-card__lead"><?= $view->e($view->t('login.lead')) ?></p>
        </div>

        <form class="form" action="/login" method="post" novalidate>
            <?= $view->csrfField() ?>
            <?php if (!empty($formError)): ?>
                <div class="alert alert--error" role="alert"><?= $view->e($view->t($formError)) ?></div>
            <?php endif; ?>
            <?php
            $field = ['id' => 'email', 'name' => 'email', 'label' => $view->t('login.email'), 'type' => 'email', 'autocomplete' => 'username', 'placeholder' => $view->t('login.email_placeholder'), 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';

            $field = ['id' => 'password', 'name' => 'password', 'label' => $view->t('login.password'), 'type' => 'password', 'autocomplete' => 'current-password', 'placeholder' => $view->t('login.password_placeholder'), 'error' => $errors['password'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';
            ?>
            <button class="button button--block" type="submit"><?= $view->e($view->t('login.submit')) ?> <span aria-hidden="true">→</span></button>
        </form>

    </div>
</section>
