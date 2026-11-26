# Wytyczne: Backend (PHP 8.3, OOP MVC)

**Agent:** `backend` · **Model: Opus 5.5** (K5 to warunek konieczny; tu są też bezpieczeństwo, transakcje i testy).
**Kryteria:** K3, K4, K5, K13, K20–K24, K27, K28, K29, K31, K33, K34 oraz logika K8. Wspiera K30 (`TranslationService`).
**Czytaj najpierw:** `AGENTS.md` §§ 5, 7, 8; `database.md` (schemat, enumy); karty S0-03…S0-05, S1-10, S2-11…S2-13, S3-14…S3-19, S6-25, S6-26, S7-27.

---

## 1. Wymogi fundamentalne (K5, K13)

- Cały kod w klasach; proceduralnie tylko `public/index.php`, `bin/cron.php` i pliki `config/*.php` (zwracają tablice/funkcje fabryczne).
- `declare(strict_types=1)` w każdym pliku. Pełne typy parametrów, zwrotów i właściwości; `readonly` dla DTO i poleceń (`final readonly class`).
- Enkapsulacja: pola `private`, dostęp przez metody. Modele niemutowalne (`with*()` zwraca kopię).
- Enumy zamiast stringów: `Role`, `IncidentStatus` (z `canTransitionTo()`), `PenaltyType`, `LicenceStatus` (`fromPoints()`), wartości zgodne z `AGENTS.md` § 7.2 i CHECK-ami w bazie.
- PSR-4 (`App\`), PSR-12. Zero `var_dump`, `die`, `@`, zmiennych globalnych i `$_POST`/`$_SESSION` poza klasami `Request` i `Session`.
- Zależności tylko przez konstruktor, zależność od interfejsów (`*RepositoryInterface`, `TransactionRunnerInterface`, `ClockInterface`, `MailerInterface`).

---

## 2. Przepływ i warstwy (K3, K28)

```text
Request → Kernel → Router → Middleware (Auth, Csrf, Role) → Controller → Service → Repository → PostgreSQL
                                                                    ├→ MailerService → SMTP
                                                                    └→ UploadService → filesystem
Response ← HTML (ViewHelper) lub JsonResponse; wyjątki → Kernel → widok błędu lub JSON {"error":…}
```

| Warstwa | Ścieżka | Odpowiada za | Nie wolno |
|---|---|---|---|
| Kernel | `src/Http/Kernel.php` | obsługa żądania, mapowanie `HttpException` na odpowiedź | logika biznesowa |
| Router | `src/Http/Router.php` | metoda + wzorzec, parametry, łańcuch middleware | SQL, widoki |
| Controller | `src/Controller/` | wejście (zwalidowane wstępnie), wywołanie serwisu, odpowiedź | SQL, pliki, mail, liczenie punktów |
| Service | `src/Service/` | reguły FIA, walidacja domenowa, transakcje, polityki | HTML, `$_*` |
| Repository | `src/Repository/` | jedyne miejsce z SQL (PDO, `prepare/execute`), mapowanie do modeli | reguły biznesowe |
| Model/DTO | `src/Model/`, `src/Service/Command/` | dane, niezmienniki | I/O |

Do utworzenia (nie istnieją): `Core/Config`, `Core/Database` (PDO + `transactional(callable)`), `Core/Logger` (`storage/logs/app.log`), `Http/Session`, `Http/Kernel`, `View/ViewHelper`, `Exception/*`, `Middleware/{Auth,Role,Csrf}Middleware`, implementacje `Pdo*Repository`, serwisy z tabeli w § 5, `config/container.php`, `config/routes.php`.

**Dostosuj modele do schematu z `database.md`:** `Incident` (reporting_user_id, accused_driver_id, opcjonalny affected_driver_id, infringement_article_id, enum statusu, bez ścieżki dowodu), `Penalty` (steward_id, article_id, `PenaltyType`, `?int sanctionValue`, expires_at), nowe `InfringementArticle`, `Evidence`, `StandingRow`.

---

## 3. Bootstrap i DI (karta S0-35)

- `config/container.php` rejestruje: `Config` (z `getenv`), `Database` (singleton), repozytoria (interfejs → implementacja PDO), serwisy, middleware, `Session`, `ViewHelper`. Router tworzony **z** kontenerem; kontrolery i middleware wyłącznie z kontenera (autowiring przez `Container::resolveClass`).
- Trasy w `config/routes.php` (tablica lub funkcja przyjmująca `Router`). `index.php` ma ~10 linii: autoloader → kontener → `Kernel::handle(Request::fromGlobals())->send()`.
- Usuń kod legacy: `src/controllers/`, fallback `require_once` w `index.php`, wrapper `Routing.php` (karta S0-34).

---

## 4. Routing (K4)

- Metody `GET`, `POST` (inne → 405). Trasa statyczna (`/login`), regex (`/lang/(?P<lang>pl|en)`), parametr ścieżki (`/incidents/{id}`), query (`?status=&gp=&q=&page=`).
- **`{id}` dopasowuje dowolny segment bez `/`, a walidacja następuje po dopasowaniu:** wartość niebędąca dodatnią liczbą całkowitą → `BadRequestException` (400); poprawny format bez rekordu → `NotFoundException` (404). Obecny wzorzec `\d+` daje 404 dla `abc` i przepuszcza `0`, popraw to.
- Brak trasy → 404, nigdy surowy wyjątek PHP. Strony błędów 400/401/403/404/422/500 w layoucie aplikacji; dla `/api/*` odpowiedź JSON z `AGENTS.md` § 7.1.
- 401 dla HTML = redirect na `/login`; 401 dla API = JSON.

---

## 5. Serwisy

| Serwis | Odpowiada za |
|---|---|
| `AuthService` | logowanie e-mailem, hash, rehash, tożsamość z bazy |
| `IncidentService` | zgłoszenie protestu (walidacje), listy ze scope’em roli, szczegóły z kontrolą własności, maszyna stanów (`investigate`, `dismiss`) |
| `PenaltyService` | `issuePenalty()` (transakcja), `expireOutdatedPenalties()` (CRON), `PenaltyPolicy::expiryFor()` |
| `LicenceService` | tabela Superlicencji z widoku, filtry, `LicenceStatus` |
| `UploadService` | walidacja i zapis dowodu |
| `MailerService` | wysyłka orzeczenia przez PHPMailer |
| `TranslationService` | `t(key, params)`, język z sesji, fallback `pl` |

### 5.1. Walidacja protestu (`IncidentService::report`)

Błędy zbierane do `ValidationException` (pole → klucz tłumaczenia + parametry), kontroler renderuje formularz ponownie z `$errors` i `$old` (status 422):
GP istnieje; `1 ≤ lap_number ≤ grands_prix.total_laps`; oskarżony kierowca istnieje i **nie jest z zespołu zgłaszającego**; opcjonalny `affected_driver_id` **jest z zespołu zgłaszającego** i różni się od oskarżonego; artykuł istnieje; opis 20–500 znaków; dowód poprawny (patrz § 7); użytkownik ma rolę `TEAM_PRINCIPAL`. Zapis sprawy i metadanych dowodu w jednej transakcji; jeśli zapis do bazy się nie uda, plik jest usuwany (kompensacja).

### 5.2. Transakcja nałożenia kary (K25, K8)

```php
public function issuePenalty(IssuePenaltyCommand $cmd, User $steward): Penalty
{
    $penalty = $this->tx->transactional(function () use ($cmd, $steward): Penalty {
        $incident = $this->incidents->findByIdForUpdate($cmd->incidentId)
            ?? throw new NotFoundException();
        $incident->status()->assertCanTransitionTo(IncidentStatus::PENALTY_ISSUED); // 422
        $this->drivers->lockById($incident->accusedDriverId());                      // SELECT … FOR UPDATE
        $issuedAt = $this->clock->now();
        $penalty  = $this->penalties->save(Penalty::issue(
            $cmd, $steward, $issuedAt, PenaltyPolicy::expiryFor($issuedAt)));       // expires_at = +365 dni
        $this->incidents->updateStatus($incident->id(), IncidentStatus::PENALTY_ISSUED);
        $points = $this->penalties->sumActivePoints($incident->accusedDriverId());
        $this->drivers->updateSuspension($incident->accusedDriverId(), $points >= LicenceStatus::LIMIT);
        return $penalty;
    });

    try {                                   // PO commicie; błąd poczty nie cofa kary, ale jest logowany
        $this->mailer->sendPenaltyDecision($penalty);
    } catch (\Throwable $e) {
        $this->logger->error('penalty mail failed', ['penalty' => $penalty->id(), 'error' => $e->getMessage()]);
    }
    return $penalty;
}
```
`Database::transactional()` robi `beginTransaction`/`commit`/`rollBack` w jednym miejscu (bez kopiowania `try/catch`). Punkty kary 0–3. Pole formularza `sanction` (`TIME_PENALTY:5` itd.) mapuje `SanctionOption` na parę (`PenaltyType`, wartość); lista opcji pochodzi z jednego miejsca w PHP i służy zarówno do renderowania radio-kart, jak i walidacji (spójnie z CHECK-iem: `TIME_PENALTY`/`GRID_DROP` wymagają wartości > 0).

### 5.3. Liczniki i Superlicencja

`LicenceStatus::fromPoints(int)` jest jedynym miejscem z progami (≥12, 9–11, 7–8, 1–6, 0). Serwis zwraca `StandingRow` z gotowym statusem, procentem paska (`points / 12`) i liczbą dni do wygaśnięcia; widok HTML i JSON korzystają z tego samego obiektu.

---

## 6. Bezpieczeństwo (K20–K24, K27)

- **Logowanie:** wyszukanie po e-mailu, `password_verify`; gdy użytkownika brak, weryfikuj względem stałego fałszywego hasha (wyrównanie czasu), jeden komunikat błędu. `password_needs_rehash` po sukcesie. Hash `PASSWORD_ARGON2ID`.
- **Sesja (`Http/Session`):** `session_set_cookie_params` (`httponly`, `samesite=Lax`, `secure` gdy `APP_ENV=production`), `use_strict_mode`, `session_regenerate_id(true)` po logowaniu. W sesji tylko `user_id` i `lang`; użytkownika i rolę ładuj z bazy w `AuthMiddleware`.
- **Wylogowanie (`POST /logout`):** `$_SESSION = []`, wygaszenie cookie z tymi samymi parametrami, `session_destroy()`, redirect na `/login`. Strony chronione z `Cache-Control: no-store, private`, żeby przycisk Wstecz nie ujawniał treści.
- **CSRF:** `CsrfMiddleware` w łańcuchu każdej trasy POST (`config/routes.php`); token `random_bytes(32)` w sesji, odczyt z pola `csrf_token` lub nagłówka `X-CSRF-Token`, `hash_equals`; brak/błąd → `CsrfTokenException` (403, kod `csrf_invalid`, teksty `error.csrf.title`/`error.csrf.message`, osobne od `forbidden` dla roli). Kolejność na trasach chronionych: `AuthMiddleware` (401) → `CsrfMiddleware` (403 `csrf_invalid`) → `RoleMiddleware` (403 `forbidden`); pilnuje tego `tests/Unit/Http/SecurityMiddlewareOrderTest.php`. Middleware zależą od interfejsów `CurrentUserProviderInterface` (`AuthService`) i `CsrfTokenStoreInterface` (`Session`).
- **Autoryzacja:** `AuthMiddleware` (401) i `RoleMiddleware::for(Role::FIA_STEWARD)` (403) w definicji trasy, dodatkowo kontrola własności w serwisie (`TEAM_PRINCIPAL` widzi sprawę, jeśli jego zespół jest zgłaszającym lub zespołem oskarżonego kierowcy; inaczej 403). Brak sprawdzenia „tylko w widoku”.
- Nagłówki i Nginx: patrz `infrastructure.md`; backend ustawia `Content-Type` i `no-store` tam, gdzie trzeba.

---

## 7. Upload dowodów (K34)

`UploadService::store(array $file): StoredEvidence`:
1. `UPLOAD_ERR_OK`, `is_uploaded_file`.
2. Rozmiar ≤ 5 MB.
3. Prawdziwy MIME przez `finfo` (`image/jpeg`, `image/png`) oraz `getimagesize` (plik jest obrazem).
4. Rozszerzenie wyprowadzone z MIME (nigdy z nazwy użytkownika); białą listę stanowią `jpg`, `png`.
5. Nazwa `bin2hex(random_bytes(16)) . '.' . $ext`, zapis `move_uploaded_file` do `public/uploads/`.
6. Zwraca ścieżkę względną, oryginalną nazwę, MIME, rozmiar i `sha256`; do bazy trafiają wyłącznie te metadane (tabela `incident_evidence`).
Test obowiązkowy: plik `.php` z nazwą `x.jpg` oraz plik z podmienionym MIME są odrzucane.

---

## 8. CRON (K32)

`bin/cron.php expire-penalties` tworzy kontener, wywołuje `CronRunner::run($argv)` → `PenaltyService::expireOutdatedPenalties(): ExpirationResult` (liczba kar, suma punktów, odwieszeni kierowcy):
w jednej transakcji `UPDATE penalties SET is_expired = TRUE WHERE NOT is_expired AND expires_at <= now() RETURNING driver_id, points_added`, potem przeliczenie `is_suspended` dla dotkniętych kierowców (`< 12` → odwieszenie). Wynik dopisywany do `storage/logs/cron.log` (UTC, liczby). Idempotentne; kod wyjścia ≠ 0 przy błędzie. Harmonogram: `infrastructure.md` § 5.

---

## 9. Poczta (K33)

`MailerService` (PHPMailer, `composer require phpmailer/phpmailer`): `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_ENCRYPTION`, `SMTP_FROM` z `Config`. Adresat: e-mail szefa zespołu ukaranego kierowcy (`users.email` zespołu) oraz, przy `notify_team`, zespołu zgłaszającego. Szablon HTML `public/views/emails/penalty_decision.html.php` (numer sprawy, artykuł, sankcja, punkty, uzasadnienie; styl pisma „FIA Stewards Decision”) i wersja tekstowa `AltBody`. Wyjątki przechwytuje wywołujący (§ 5.2). Lokalnie wiadomości widać w Mailpit (`:8025`).

---

## 10. Tłumaczenia (K30, strona backendu)

`TranslationService::t(string $key, array $params = []): string` czyta `config/lang/{pl,en}.php` (płaskie tablice klucz → tekst z `:param`). Brak klucza w języku → fallback `pl` → sam klucz oraz wpis w logu. Walidacja i wyjątki zwracają **klucze**, nie teksty, więc zmiana języka obejmuje komunikaty błędów. Test sprawdza, że `pl.php` i `en.php` mają identyczny zestaw kluczy.

---

## 11. Testy (K31)

- `composer require --dev phpunit/phpunit`; `phpunit.xml`, `tests/Unit/`; skrypt `composer test`.
- Izolacja: fałszywe repozytoria w pamięci, `FakeClock`, `FakeTransactionRunner` (do sprawdzenia rollbacku), `FakeMailer`; bez bazy i sieci.
- Minimalny zestaw (poprawny + błędny przypadek): `IncidentServiceTest` (poprawny protest, oskarżony z własnego zespołu, zły numer okrążenia, niedozwolone przejście statusu), `PenaltyServiceTest` (naliczenie punktów, zawieszenie przy ≥ 12, rollback przy błędzie w połowie, błąd poczty nie cofa kary, wygaszanie i odwieszenie), `UploadServiceTest` (poprawny PNG, `.php` pod `.jpg`, zły MIME, za duży plik), `LicenceStatusTest` (progi), `RouterTest` (400/404 dla `{id}`), `TranslationKeysTest`.
- Uruchomienie: `docker compose exec php composer test`.

---

## 12. Definicja ukończenia i czego nie robić

Gotowe, gdy: spełnione są kryteria z karty, `php -l` czysty, `composer test` zielony, logi PHP bez warningów, brak SQL poza repozytoriami, brak tekstów użytkowych w kodzie, a zmiany kontraktu (trasy, JSON, enumy) są odzwierciedlone w `AGENTS.md` § 7.
Nie rób: nie edytuj CSS/JS (od tego jest `frontend`), nie zmieniaj schematu bez agenta `database`, nie osłabiaj walidacji pod wygodę testów, nie wstawiaj danych makietowych do kontrolerów.
