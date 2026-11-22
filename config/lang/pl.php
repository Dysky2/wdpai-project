<?php

declare(strict_types=1);

/** Polish UI texts. Keys must match config/lang/en.php exactly (enforced by TranslationKeysTest). */
return [
    // Layout, navigation, footer
    'app.name' => 'ApexSteward',
    'app.skip_to_content' => 'Przejdź do treści',
    'app.brand_aria' => 'F1 Stewards, strona główna',
    'app.brand_title' => 'F1 STEWARDS',
    'app.brand_tagline' => 'Portal zarządzania incydentami',
    'app.footer_left' => 'ApexSteward · FIA Stewards Hub',
    'app.footer_right' => 'Konsola orzecznictwa',
    'nav.main_aria' => 'Główna nawigacja',
    'nav.mobile_aria' => 'Nawigacja mobilna',
    'nav.incidents' => 'Incydenty',
    'nav.standings' => 'Klasyfikacja',
    'nav.new_protest' => 'Nowy protest',
    'nav.logout' => 'Wyloguj',
    'nav.language_aria' => 'Język interfejsu',
    'theme.toggle_label' => 'Ciemny motyw',
    'theme.toggle_title' => 'Zmień motyw',
    'role.TEAM_PRINCIPAL' => 'Szef zespołu',
    'role.FIA_STEWARD' => 'Sędzia FIA',
    'pagination.aria' => 'Paginacja',
    'pagination.prev' => 'Poprzednia',
    'pagination.next' => 'Następna',

    // Error pages
    'error.eyebrow' => 'KOMUNIKAT SYSTEMOWY / :status',
    'error.back_home' => 'Wróć na stronę główną',
    'error.400.title' => 'Nieprawidłowe żądanie',
    'error.400.message' => 'Adres lub dane żądania są niepoprawne. Sprawdź link i spróbuj ponownie.',
    'error.401.title' => 'Wymagane logowanie',
    'error.401.message' => 'Aby zobaczyć tę stronę, musisz się zalogować.',
    'error.403.title' => 'Brak dostępu',
    'error.403.message' => 'Twoja rola nie pozwala na wykonanie tej operacji.',
    'error.404.title' => 'Nie znaleziono strony',
    'error.404.message' => 'Wybrana strona nie istnieje albo została przeniesiona. Wróć do centrum dowodzenia i wybierz inną sekcję.',
    'error.422.title' => 'Nie można przetworzyć żądania',
    'error.422.message' => 'Dane są poprawne składniowo, ale nie można ich zaakceptować.',
    'error.500.title' => 'Błąd serwera',
    'error.500.message' => 'Coś poszło nie tak po naszej stronie. Spróbuj ponownie za chwilę.',

    // Page titles
    'incidents.page_title' => 'Dziennik incydentów',
    'report.page_title' => 'Nowy protest',
    'standings.page_title' => 'Klasyfikacja punktów karnych',
    'adjudicate.page_title' => 'Orzeczenie :code',

    // Login
    'login.page_title' => 'Logowanie',
    'login.eyebrow' => 'FIA / RACE CONTROL',
    'login.heading' => 'Zaloguj się do panelu',
    'login.lead' => 'Wprowadź dane, aby kontynuować pracę.',
    'login.email' => 'Adres e-mail',
    'login.email_placeholder' => 'np. toto@mercedes.f1',
    'login.password' => 'Hasło',
    'login.password_placeholder' => 'Wprowadź hasło',
    'login.submit' => 'Zaloguj się',
    'login.invalid_credentials' => 'Nieprawidłowy e-mail lub hasło',

    // Statuses
    'status.REPORTED' => 'Zgłoszony',
    'status.UNDER_INVESTIGATION' => 'W trakcie dochodzenia',
    'status.PENALTY_ISSUED' => 'Kara nałożona',
    'status.NO_FURTHER_ACTION' => 'Bez dalszych działań',
    'licence_status.SUSPENDED' => 'Zawieszony',
    'licence_status.AT_BAN_RISK' => 'Ryzyko zawieszenia',
    'licence_status.CAUTION' => 'Ostrzeżenie',
    'licence_status.ACTIVE' => 'Aktywna',
    'licence_status.CLEAN' => 'Czysta',
];
