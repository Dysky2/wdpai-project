# Wytyczne: Frontend (HTML, CSS, JS)

**Agent:** `frontend` · **Model: Sonnet 5.5** (wykonanie według gotowych prototypów i kontraktów; wynik da się zweryfikować wizualnie).
**Kryteria:** K9 (HTML), K14 (JS), K15 (Fetch), K16 (design), K17 (RWD), K18 (dark/light), K19 (WCAG), K30 (słowniki i przełącznik języka). Wspiera K28 (partiale zamiast kopii HTML).
**Czytaj najpierw:** `AGENTS.md` §§ 7, 8; **prototypy `docs/ui/`**; karty S0-05, S3-19, S4-20…S4-23, S5-24.
**Nie zmieniasz:** tras, nazw pól, kształtu JSON, schematu, enumów (kontrakt z `AGENTS.md` § 7). Potrzebujesz zmiany → zgłoś w komentarzu i zatrzymaj się.

---

## 1. Źródło prawdy o wyglądzie

`docs/ui/Dawid_Molik_WDPAI_Prototypy.pdf` (Figma, 10 stron) oraz PNG `incydents.png`, `driverStandings.png`, `showMoreInfoAboutPenalty.png`. Strony PDF: 1–5 desktop, 6–10 mobile w tej samej kolejności. Przed pracą nad ekranem otwórz odpowiednią stronę; po pracy zrób zrzut (375, 768, 1440 px) i porównaj.

| Ekran | PDF desktop / mobile | Trasa | Widok | Rola |
|---|---|---|---|---|
| Grand Prix Incidents Log | 1 / 6 | `/incidents` | `incidents/index.html.php` | obie |
| Incident Dossier | 2 / 7 | `/incidents/{id}` | `incidents/show.html.php` | obie (akcje: steward) |
| Adjudicate Penalty | 3 / 8 | `/stewards/adjudicate/{id}` | `stewards/adjudicate.html.php` | steward |
| Lodge New Incident Report / Team Protest | 4 / 9 | `/incidents/create` | `incidents/create.html.php` | team principal |
| Driver Super Licence Penalty Points Standings | 5 / 10 | `/standings` | `standings/index.html.php` | obie |
| Logowanie, strony błędów 400/401/403/404/422/500 | brak prototypu | `/login`, błędy | `login.html.php`, `errors/*.html.php` | gość / wszyscy |

Logowanie i błędy projektuj w tym samym języku wizualnym (wyśrodkowana karta na ciemnym tle, czerwony przycisk, baner `role="alert"`).

### Do naprawy względem obecnego kodu
- Układ z sidebarem i ekran „Dashboard / Sprawy / O panelu” nie istnieje w prototypie. Zastąp go **górnym paskiem** (desktop) i **dolnym paskiem zakładek** (mobile). Ekranem startowym jest Incidents Log (`/` → `/incidents`).
- Usuń widoki-placeholdery (`about`, `projects`, `project`), dane makietowe z `home.html.php` i stopkę „CS304 Web Dev Project” z prototypu; stopka: „ApexSteward · FIA Stewards Hub” + wersja z konfiguracji.
- Elementy ozdobne z prototypu bez danych (np. „SERVER: SQL_OK (23ms)”, „SESSION: MON_2024_R1”) pomiń albo pokazuj tylko prawdziwe wartości. Nie wstawiaj zmyślonych liczb na stałe.

---

## 2. Design tokens (próbki z PNG; dopracuj pipetą)

Motyw ciemny jest domyślnym wyglądem prototypu. Jasny motyw wyprowadź z tej samej struktury tokenów.

```css
:root {                                   /* jasny: wartości startowe, sprawdź kontrast */
  --bg: #f6f6f8;  --surface: #ffffff;  --surface-2: #ececf0;  --input-bg: #ffffff;
  --text: #15151e; --text-muted: #5b5b66; --border: #d9d9e0;
  --accent: #e10600; --accent-hover: #b30500; --accent-contrast: #ffffff;
  --accent-soft: #b35b54;                /* awatar, akcenty ciepłe */
  --st-reported-fg: #1d4f9c;       --st-reported-bg: #dce8fb;
  --st-investigation-fg: #7a4b00;  --st-investigation-bg: #ffe9c7;
  --st-penalty-fg: #93000a;        --st-penalty-bg: #ffdad6;
  --st-closed-fg: #3d3d46;         --st-closed-bg: #e3e3e8;
  --font-sans: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  --font-serif: Georgia, "Times New Roman", serif;   /* eyebrow, etykiety tabel, stopka */
  --radius: 8px; --space: 8px;
}
[data-theme="dark"] {                     /* z prototypu */
  --bg: #121316;  --surface: #1b1b1f;  --surface-2: #292a2d;  --input-bg: #0d0e11;
  --text: #e8e8ea; --text-muted: #9a9aa5; --border: #343538;
  --accent: #e10600; --accent-hover: #ff2a1f; --accent-contrast: #ffffff;
  --accent-soft: #e8a099;                /* podtytuły, nagłówki tabel, nazwy zespołów */
  --st-reported-fg: #adc6ff;       --st-reported-bg: #172a44;
  --st-investigation-fg: #ffb95f;  --st-investigation-bg: rgba(255,185,95,.14);
  --st-penalty-fg: #ffdad6;        --st-penalty-bg: #93000a;
  --st-closed-fg: #c9c9cf;         --st-closed-bg: #343538;
}
```

Statusy Superlicencji: `SUSPENDED` czerwone tło `--accent`, `AT_BAN_RISK` jak kara, `CAUTION` jak „investigation”, `ACTIVE` jak „closed”, `CLEAN` jak „reported”. Kolor paska postępu: czerwony (≥ 9 pkt), bursztynowy (7–8), ciepły neutralny (1–6); tor `--surface-2`. Status zawsze **także tekstem** (nie tylko kolorem).
Typografia: nagłówki sans, grube; „eyebrow” (np. `● FIA EVENT ADJUDICATION QUEUE`) w serifie, małe, wersaliki, czerwona kropka. Bez zewnętrznych CDN (CSP, tryb offline); Inter opcjonalnie self-hosted w `public/fonts/`.

---

## 3. Layout i nawigacja

- `layout.html.php`: `<html lang>` z języka sesji, skrypt anty-FOUC w `<head>` (już jest: zachowaj), `<meta name="csrf-token">`, link do jednego arkusza głównego, partiale `header`, `flash`, `footer`, `<main id="main">` z linkiem „Przejdź do treści” na początku.
- **Desktop (≥ 1024 px):** pasek górny: znak „F1 STEWARDS / Incident Management Portal” (czerwony pionowy akcent), linki `Incidents`, `Driver Standings`, `New Report` (tylko `TEAM_PRINCIPAL`), po prawej chip użytkownika (`FIA Steward (John D.)` = etykieta roli + imię i inicjał), `Logout` (formularz POST ze stylem linku), okrągły awatar; do tego przełącznik motywu i języka (nie ma ich w prototypie; małe, spójne ikonowe przyciski).
- **Mobile (< 768 px):** nagłówek „ApexSteward” + czerwony znacznik aktywnego GP + podpis `● ROLA • sekcja`, awatar po prawej; **dolny pasek zakładek** z ikoną i etykietą, aktywna na czerwono. Zakładki zależne od roli: `TEAM_PRINCIPAL` = Incidents, Protest, Standings; `FIA_STEWARD` = Incidents, Standings (Details i Adjudicate to akcje wewnątrz sprawy, nie zakładki; to świadome odstępstwo od prototypu, bo prototyp pokazuje wszystkie naraz).
- Tablet (768–1023 px): górny pasek jak na desktopie, kafelki KPI 2×2.

---

## 4. Komponenty (jeden arkusz `style.css` lub podział `base/components/pages`, zero stylów inline)

Karty KPI (ikona, etykieta, wartość, podpis) • chipy filtrów z licznikami (`All Incidents (12)`) • pole wyszukiwania z ikoną • tabela danych (nagłówki serifowe, wersaliki, `scope="col"`) • badge statusu (ikona + tekst) • chip okrążenia `L24` • pasek postępu z etykietą `%` • przycisk główny (czerwony), drugorzędny (`Details`), linkowy • karta formularza z nagłówkiem dokumentu (`DOC 48`) • radio-karty sankcji • stepper punktów `− 2 +` • textarea z licznikiem znaków • dropzone z podglądem i chipem pliku (`READY TO UPLOAD`, `✕`) • pasek akcji przyklejony na mobile • modal potwierdzenia • baner informacyjny/błędu • paginacja.
Nazewnictwo BEM-lite (`.card`, `.card--kpi`, `.badge--penalty`). Powtarzalny HTML trafia do partiali (`partials/kpi-card`, `status-badge`, `form-field`, `pagination`, `evidence-dropzone`), nie jest kopiowany (K28).

---

## 5. Zawartość ekranów (dane przychodzą z kontrolera; nie licz niczego w widoku)

**Incidents Log.** Nagłówek „Grand Prix Incidents Log”, „Active session: Race (R) • {total_laps} Laps”; KPI: Total Logged, Reported, Under Investigation, Penalties Issued; filtry: lista GP, wyszukiwarka (kierowca/kod/`INC-…`), chipy `All / Reported / Under Investigation / Closed·Decided`; tabela: LAP, INCIDENT ID, INVOLVED DRIVERS (`Car 1 (VER) vs Car 44 (HAM)` lub pojedynczy), CATEGORY / DESCRIPTION (tytuł artykułu + skrócony opis), STATUS (badge, dla kary np. „+5s Penalty”), ACTION (`Details`); paginacja 6 na stronę z podpisem „Showing 6 of 12 incidents”. Mobile: karty zamiast tabeli (chip okrążenia, badge, `#1 Verstappen vs #44 Hamilton`, opis, `View Details →`), chipy przewijane poziomo.

**Incident Dossier.** Okruszki, tytuł „Incident Dossier: INC-042”, czas zgłoszenia; siatka: Grand Prix, Session progress (`Lap 24 / 57`), zgłaszający zespół; „Drivers involved” (oskarżony i opcjonalnie zgłaszający kierowca); „Alleged infringement” (tytuł + referencja artykułu); „Steward incident summary” (opis); „Submitted Photo Evidence” (obraz, podpis: nazwa pliku • rozmiar • SHA-256 z bazy; `alt` opisowy). Kolumna boczna: „Adjudication Status” (badge) i, tylko dla `FIA_STEWARD`, „Steward Actions”: `Mark Under Investigation` (aktywne dla `REPORTED`, Fetch POST), `Issue Penalty` (link do orzekania, aktywne dla `UNDER_INVESTIGATION`), `Dismiss Case` (Fetch POST po potwierdzeniu w modalu, `UNDER_INVESTIGATION`). Nie renderuj „Request Additional Statement” ani „Assigned stewards” (poza zakresem). Po orzeczeniu pokaż blok kary (typ, punkty, artykuł, uzasadnienie).

**Adjudicate Penalty.** Okruszki `Incidents / INC-042 / Adjudicate Penalty`, znacznik „SESSION: LIVE ADJUDICATION”, nagłówek „Official Stewards Penalty Determination” + `DOC {id}`; pola tylko do odczytu: Incident ID, Penalized Driver; select „Infringement regulation / article” (mandatory; opcje z `data-*` presetu); radio-karty `sanction` (5 s, 10 s, 3-place grid drop, drive-through, formal reprimand, DSQ; wartości `TIME_PENALTY:5` itd. z serwera); stepper punktów 0–3 z tekstem „Current driver balance: 4 pts → New projected balance: 6 pts” (liczone w JS z `data-current-points`) i małym paskiem; textarea uzasadnienia z licznikiem i linkiem „Insert Precedent Wording”; checkbox „Notify team principals (…)”; `Cancel / Return to Case` i `Save & Apply Penalty` (po kliknięciu modal potwierdzenia). Zmiana artykułu ustawia sugerowaną sankcję i punkty (nadal edytowalne). Działa też bez JS (wartości domyślne z serwera).

**Lodge New Incident Report / Team Protest.** Eyebrow „FIA ADJUDICATION PROTOCOL • ISC ART. 13”; baner z terminem zgłoszenia (tekst z tłumaczeń); pola: Grand Prix session (domyślnie aktywny), Lap number (`/ {total_laps} max`), Lodging competitor team (tylko do odczytu), Accused driver (bez kierowców własnego zespołu), Your driver involved (opcjonalnie, tylko własny zespół), Alleged infringement type, Statement of facts (licznik „x / 500”), Attach photo evidence (dropzone JPG/PNG ≤ 5 MB z podglądem), Authorized team representative (tylko do odczytu: imię i rola zalogowanego); przycisk `Submit Official Protest to Stewards`. „Save Draft” i zegar sesji pomiń.

**Standings.** „Driver Super Licence Penalty Points Standings”; KPI: Total Tracked, Critical Threshold (kierowca z największą liczbą punktów, `10/12 pts`, „2 pts to ban”), Pending Inquiry (liczba spraw w toku), Clean Record (liczba i % stawki); filtry `All Drivers / At Risk (≥7 pts) / Clean Licence (0 pts)` i wyszukiwarka; tabela: POS, CAR #, DRIVER NAME, TEAM, CURRENT POINTS (`10 / 12`), PROGRESS TO BAN (pasek + %), EARLIEST EXPIRY (data + „18 days remaining”), STATUS. Mobile: karty z paskiem, banerem „1 Driver at Imminent Ban Risk” i polem „Earliest expiry”. „Download Official CSV” pomiń.

---

## 6. JavaScript (K14, K15): moduły ES w `public/js/`, jeden `<script type="module" src="/js/app.js" defer>`

| Moduł | Zadanie |
|---|---|
| `theme.js` | przełącznik motywu (istnieje; zachowaj `data-theme`, `localStorage`, `prefers-color-scheme`) |
| `api.js` | jedna funkcja `request(method, url, {body})`: nagłówek `X-CSRF-Token` z meta, `Accept: application/json`, parsowanie JSON, rzucanie `ApiError(status, code, message)`; żadnych innych `fetch` w projekcie |
| `validator.js` | reguły pól (wymagane, zakres, długość, plik) i wyświetlanie błędów pod polem (`aria-describedby`, `aria-invalid`, region `aria-live="polite"`) |
| `incidents.js` | filtry i wyszukiwarka dziennika: `GET /api/incidents?…` → przebudowa wierszy/kart, `aria-busy`, obsługa błędów; aktualizacja adresu przez `history.replaceState` |
| `stewards.js` | `Mark Under Investigation` i `Dismiss Case`: `POST /api/incidents/{id}/investigate|dismiss`, stan ładowania na przycisku, aktualizacja badge i dostępnych akcji z odpowiedzi, komunikaty dla 403/422/500 |
| `report-form.js` | walidacja protestu (okrążenie ≤ max, opis 20–500, plik JPG/PNG ≤ 5 MB), licznik znaków, dropzone z podglądem (`URL.createObjectURL`, zwalnianie w `revokeObjectURL`) |
| `adjudicate.js` | stepper punktów, przeliczanie „projected balance”, presety z artykułu, wstawianie `precedent_wording`, licznik znaków |
| `modal.js` | dostępny modal (fokus w środku i powrót, `Escape`, `aria-modal`) dla potwierdzeń |

Zasady: żadnych `innerHTML` z danymi z odpowiedzi (używaj `textContent` / `createElement`, XSS); brak `console.log` w kodzie końcowym; skrypty nie są wymagane do podstawowego działania formularzy (serwer waliduje wszystko ponownie). Obsługa błędów API: pokaż komunikat z `error.message` (już przetłumaczony przez serwer) w `role="alert"`.

---

## 7. Responsywność (K17), motyw (K18), WCAG (K19), i18n (K30)

- **Breakpointy:** mobile < 768, tablet 768–1023, desktop ≥ 1024. Pięć widoków z tabeli § 1 działa przy 320, 375, 768, 1440 px bez poziomego przewijania; tabele danych przechodzą w karty na mobile.
- **Motyw:** `data-theme` na `<html>`, `localStorage`, `prefers-color-scheme` przy pierwszej wizycie, blokujący skrypt w `<head>` (jest). Test: twarde odświeżenie w ciemnym motywie bez białego mignięcia.
- **WCAG 2.1 AA:** cała obsługa klawiaturą, widoczny `:focus-visible` (kontrastowy obrys), `<label for>` przy każdym polu, `alt` dla obrazów (dowód: „Klatka z onboardu: …”, dekoracje `alt=""`), kontrast ≥ 4.5:1 tekstu i ≥ 3:1 elementów UI w **obu** motywach, `role="progressbar"` z `aria-valuenow/min/max` (0–12) i etykietą tekstową, status nie tylko kolorem, `<th scope>`, hierarchia nagłówków (jeden `<h1>`), `lang` na `<html>`. Audyt Lighthouse/axe i raport do README robi `docs-writer`, a Ty usuwasz znalezione problemy.
- **i18n:** każdy tekst przez `$view->t('klucz')`; dodajesz klucze do **obu** plików `config/lang/pl.php` i `en.php` (identyczny zestaw). Etykiety statusów i kar: `status.*`, `penalty_type.*`, `licence_status.*`. Prototyp jest po angielsku; domyślny język interfejsu to polski, więc angielskie napisy z prototypu są tłumaczeniem `en`.

---

## 8. HTML i bezpieczeństwo w widokach (K9)

Semantyka (`header`, `nav`, `main`, `section`, `article`, `footer`, `figure`/`figcaption` dla dowodu), `type`/`inputmode`/`autocomplete`/`required`/`maxlength`/`accept="image/png,image/jpeg"`, błędy pod polami. Wszystko wypisywane przez `$view->e()`; formularze POST z `$view->csrfField()`; wylogowanie to formularz POST, nie link. Widoki bez logiki: tylko `foreach` i `if`.

---

## 9. Definicja ukończenia i czego nie robić

Gotowe, gdy: ekran wygląda jak prototyp w obu rozmiarach (zrzuty porównane), działa w obu motywach i językach, klawiaturą da się wykonać cały przepływ, konsola przeglądarki jest czysta, a logi PHP bez warningów.
Nie rób: nie zmieniaj kontraktów, tras ani PHP poza widokami i `ViewHelper` (uzgodnij z `backend`), nie dodawaj bibliotek JS/CSS ani CDN, nie kopiuj stałych danych z prototypu do widoków, nie implementuj funkcji spoza zakresu bez decyzji.
