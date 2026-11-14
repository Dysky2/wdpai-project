<section class="card panel-card" aria-labelledby="register-heading">
    <div class="card__body">
        <div class="panel-card__header">
            <p class="eyebrow">NOWY PROFIL</p>
            <h1 id="register-heading">Utwórz konto</h1>
            <p class="panel-card__lead">Zacznij pracę w centrum dowodzenia FIA.</p>
        </div>

        <form class="form" action="/register" method="post" novalidate>
            <div class="form__row">
                <?php
                $field = ['id' => 'name', 'name' => 'name', 'label' => 'Imię', 'autocomplete' => 'given-name', 'placeholder' => 'np. Maria', 'value' => $old['name'] ?? '', 'error' => $errors['name'] ?? null];
                require __DIR__ . '/partials/form-field.html.php';

                $field = ['id' => 'surname', 'name' => 'surname', 'label' => 'Nazwisko', 'autocomplete' => 'family-name', 'placeholder' => 'np. Kowalska', 'value' => $old['surname'] ?? '', 'error' => $errors['surname'] ?? null];
                require __DIR__ . '/partials/form-field.html.php';
                ?>
            </div>
            <?php
            $field = ['id' => 'email', 'name' => 'email', 'label' => 'Adres e-mail', 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'np. maria@example.com', 'value' => $old['email'] ?? '', 'error' => $errors['email'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';

            $field = ['id' => 'password', 'name' => 'password', 'label' => 'Hasło', 'type' => 'password', 'autocomplete' => 'new-password', 'minlength' => 8, 'hint' => 'Minimum 8 znaków', 'error' => $errors['password'] ?? null];
            require __DIR__ . '/partials/form-field.html.php';
            ?>
            <button class="button button--block" type="submit">Zarejestruj się <span aria-hidden="true">→</span></button>
        </form>

        <p class="panel-card__footer">Masz już konto? <a href="/login">Zaloguj się</a></p>
    </div>
</section>
