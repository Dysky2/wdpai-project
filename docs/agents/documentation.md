# Wytyczne: Dokumentacja i audyt jakości

Dwa agenty, dwa modele:
- `docs-writer` · **Sonnet 5.5**: README, zrzuty ekranu, raport WCAG, tabela Security Bingo, opis CRON/poczty i deployu. Praca redakcyjna z gotowych faktów.
- `qa-auditor` · **Opus 5.5**, tylko odczyt: niezależny przegląd K1–K36 przed PR i przed oddaniem. Wymaga oceny, nie pisania.

**Czytaj najpierw:** `AGENTS.md`, `kryteria.md`, karty S7-28, S8-30, S8-31, S8-32.

---

## 1. `docs-writer`: README.md (K1)

Zasada nadrzędna: **piszesz tylko to, co istnieje i działa.** Fakty bierz z kodu, `docker compose`, Trello i `AGENTS.md`; nie opisuj funkcji, których nie uruchomiłeś. Zrzuty pokazują realną aplikację, nie prototyp z `docs/ui`.

Struktura (kolejność z `kryteria.md`): Autorzy i zakres odpowiedzialności (zapytaj użytkownika o autorów i podział) → Opis problemu i główne funkcje → Zrzuty ekranu (≥ 5 mobile + ≥ 5 desktop) → Architektura (diagram i tabela warstw, już w README, aktualizuj po zmianach) → ERD (blok Mermaid skopiowany z `database.md`, zgodny ze schematem) → Routing / endpointy (tabela z `AGENTS.md` § 7.1) → Uruchomienie przez Docker → Zmienne środowiskowe → Import bazy i dane demo (konta, jawne hasło demo) → Testy (`docker compose exec php composer test`) → Raport WCAG → Security Bingo → Zadanie CRON i wysyłka e-maila → Deploy (link, opis, data weryfikacji).

### Zrzuty ekranu
- Katalog `docs/screenshots/`, nazwy `desktop-01-incidents.png`, `mobile-01-incidents.png` itd. (sprawdź, że `docs/` nie jest w `.gitignore`, karta S0-36).
- Szerokości: desktop 1440 px, mobile 375 px; oba motywy dla co najmniej jednego widoku. Zrób je przeglądarką (Playwright lub wbudowana przeglądarka) na działającej aplikacji z danymi z seeda, zalogowany odpowiednią rolą. Pliki PNG, bez danych wrażliwych.
- W README obrazy ze ścieżką względną i tekstem `alt`.

### Raport WCAG (K19)
Narzędzie: Lighthouse (Chrome) lub axe. Wpis w README: data, wersja narzędzia, wynik *Accessibility* (cel ≥ 95), zrzut raportu, tabela „problem → naprawa”. Dla każdej nienaprawionej uwagi opis powodu. Błędy zgłaszasz agentowi `frontend` (nie poprawiasz CSS/JS sam).

### Security Bingo (K27)
Tabela: `Zabezpieczenie | Gdzie w kodzie (plik:linia) | Jak zweryfikować`. Wiersze minimum: hashowanie haseł, zapytania parametryzowane, walidacja danych, kodowanie wyjścia (XSS), autoryzacja po stronie serwera, CSRF, bezpieczny upload, ciasteczka sesji, sekrety w `.env`, ograniczenie wykonywania PHP w Nginx. Każde odwołanie `plik:linia` sprawdź ręcznie; wiersz bez dowodu usuń zamiast zgadywać.

### CRON i poczta
Polecenie, harmonogram, sposób ręcznego testu, gdzie szukać logu (`storage/logs/cron.log`), jak zobaczyć e-mail w Mailpit (`http://localhost:8025`).

---

## 2. `qa-auditor`: protokół audytu

1. **Najpierw bramka K5.** Jeśli backend nie jest obiektowy (kod legacy w `src/controllers/`, funkcje poza klasami, brak `strict_types`), wynik to 2.0; zgłoś i zatrzymaj punktację.
2. Dla każdego K1–K36 podaj ocenę 1 / 0,5 / 0 według `kryteria.md`, **dowód** (`plik:linia` lub wynik polecenia) i lukę. Bez dowodu ocena to 0.
3. Zasada pewności: oceniaj tylko to, co zweryfikowałeś. Gdy czegoś nie da się uruchomić w Twoim środowisku, napisz „niezweryfikowane” i podaj komendę dla człowieka.
4. Wynik: tabela K / ocena / dowód / luka / karta Trello, suma punktów i przewidywana ocena, lista 5 najtańszych poprawek (największy zysk punktowy na jednostkę pracy). Niczego nie edytujesz.

### Jak sprawdzać (skrót)

| K | Dowód |
|---|---|
| 1 | sekcje README wg § 1; zrzuty realne; link do deployu |
| 2 | test „czysty klon” z `infrastructure.md` § 7 |
| 3 | diagram w README; `grep -rnE "SELECT\|INSERT\|UPDATE" src/Controller` puste |
| 4 | `curl -i /incidents/abc` → 400, `/incidents/0` → 400, `/incidents/999999` → 404, `/lang/pl`, `?status=` |
| 5 | `grep -rL strict_types src`; brak `src/controllers/`; `grep -rnE "^function " src` puste |
| 6 | blok `erDiagram` w README zgodny z `db/schema.sql` kolumna po kolumnie |
| 7 | `git log --format=%s` (prefiksy), `git shortlog -sn`, merge do `main` |
| 8 | pełny scenariusz demo (3 min) bez ręcznych poprawek |
| 9 | partiale `layout`/`header`/`footer`; `<label for>`; `grep -rn 'style=' public/views` puste |
| 10 | `prepare(` w repozytoriach; brak konkatenacji zmiennych w SQL; połączenie z `.env` |
| 11 | liczba tabel, FK, CHECK, 3NF (bez powtórzeń danych) |
| 12 | `docker compose down -v && up` ładuje `schema.sql` + `seed.sql` bez błędów |
| 13 | `php -l`, brak warningów w logach, typy w sygnaturach |
| 14 | moduły w `public/js/`, walidacja po stronie klienta działa w przeglądarce |
| 15 | `grep -rn "fetch(" public/js` tylko w `api.js`; GET i POST bez przeładowania, obsługa błędów |
| 16 | style w CSS, spójność wizualna z `docs/ui` |
| 17 | 5 widoków × 320/768/1440 px, brak poziomego przewijania |
| 18 | `data-theme`, `localStorage`, `prefers-color-scheme`, skrypt w `<head>`, brak FOUC |
| 19 | wynik Lighthouse ≥ 95 + raport w README, nawigacja klawiaturą |
| 20 | logowanie kontem z bazy, hash Argon2id, jeden komunikat błędu |
| 21 | sesja po zmianie podstron; `curl -i` pokazuje flagi cookie; `session_regenerate_id` |
| 22 | `curl` jako team principal na `/stewards/adjudicate/1` → 403; bez sesji → 401/redirect; POST bez CSRF → 403 |
| 23 | dwa konta o różnym UI i uprawnieniach; role w bazie |
| 24 | logout + Wstecz nie przywraca dostępu; cookie wygaszone |
| 25 | widok, trigger lub transakcja w `schema.sql`/serwisie i pokazane na demo; test rollbacku |
| 26 | JOIN-y w repozytoriach; tabela `ON DELETE` z uzasadnieniem |
| 27 | tabela Security Bingo, każdy dowód `plik:linia` sprawdzony |
| 28 | `grep` powtórzonych zapytań/walidacji; opcjonalnie `npx jscpd src public` |
| 29 | krótkie metody, brak `var_dump`/`console.log`/`TODO`, spójne PSR-12 |
| 30 | `pl.php` i `en.php` mają te same klucze; przełącznik zmienia także walidację |
| 31 | `composer test` zielony; przypadki poprawne i błędne |
| 32 | dwa uruchomienia `bin/cron.php expire-penalties` (idempotencja), wpis w logu, harmonogram |
| 33 | e-mail widoczny w Mailpit; konfiguracja SMTP z środowiska |
| 34 | `.php` podszyty pod `.jpg` odrzucony; nazwa losowa; w bazie tylko ścieżka |
| 35 | `git grep -inE "password\|secret\|token"`; `.env` poza gitem; `.env.example` |
| 36 | publiczny URL działa, logowanie demo, wersja zgodna z `main` |

---

## 3. Definicja ukończenia i czego nie robić

`docs-writer`: README spełnia § 1, każdy link i obraz działa, polecenia z README wykonano w czystym klonie. Nie wymyślaj funkcji, wyników testów ani liczb; nie zmieniaj kodu aplikacji.
`qa-auditor`: raport z dowodami dla każdego K, bez edycji plików, z wyraźnym oznaczeniem rzeczy niezweryfikowanych.
