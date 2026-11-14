<section class="card panel-card" aria-labelledby="login-heading">
    <div class="card__body">
        <div class="panel-card__header">
            <p class="eyebrow">FIA / RACE CONTROL</p>
            <h1 id="login-heading">Zaloguj się do panelu</h1>
            <p class="panel-card__lead">Wprowadź dane, aby kontynuować pracę.</p>
        </div>

        <form class="form" action="/login" method="post" novalidate>
            <?php
            $field = ['id' => 'email', 'name' => 'email', 'label' => 'Adres e-mail', 'type' => 'email', 'autocomplete' => 'username', 'placeholder' => 'np. toto@mercedes.f1', 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';

            $field = ['id' => 'password', 'name' => 'password', 'label' => 'Hasło', 'type' => 'password', 'autocomplete' => 'current-password', 'placeholder' => 'Wprowadź hasło', 'error' => $errors['password'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';
            ?>
            <button class="button button--block" type="submit">Zaloguj się <span aria-hidden="true">→</span></button>
        </form>

        <p class="panel-card__footer">Nowy użytkownik? <a href="/register">Utwórz konto</a></p>
    </div>
</section>
