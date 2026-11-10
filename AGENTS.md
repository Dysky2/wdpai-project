# AGENTS.md — ApexSteward (FIA Stewards Hub)

Ten plik jest głównym przewodnikiem dla agentów AI pracujących w tym repozytorium. Przed wprowadzeniem jakichkolwiek zmian zapoznaj się z tym plikiem, dokumentacją domeny w karcie projektu oraz szczegółowymi kryteriami w `kryteria.md`.

---

## 1. Cel i domena projektu

**Oficjalny tytuł:** Zarządzanie incydentami wyścigowymi i orzecznictwem sędziowskim FIA w Formule 1  
**Nazwa systemowa:** ApexSteward (FIA Stewards Hub)  
**Domena:** Motorsport, obieg spraw dyscyplinarnych sędziów FIA, ewidencja punktów karnych Superlicencji kierowców F1.

Projekt rozwiązuje trzy kluczowe problemy:
1. **Brak czytelnego rejestru incydentów:** Zastępuje ulotne paski TV i surowe pliki PDF ustandaryzowaną, interaktywną platformą webową.
2. **Cyfrowy obieg materiału dowodowego:** Zespoły załączają telemetrię i klatki onboardu (pliki graficzne z walidacją MIME/rozmiaru) bezpośrednio do zgłoszeń.
3. **Automatyczne i atomowe orzecznictwo:** Kary, punkty Superlicencji (wygasające po 365 dniach) i zawieszenia kierowców (limit 12 pkt) rozliczane są transakcyjnie (ACID) oraz cyklicznie przez proces CRON w CLI.

> [!IMPORTANT]
> **Warunek konieczny zaliczenia (K5):** Część backendowa **musi być w 100% obiektowa** (MVC, Service, Repository, DTO/Entity, interfejsy, enkapsulacja, `declare(strict_types=1)`). Kod strukturalny/proceduralny poza punktem wejścia (`public/index.php`) i konfiguracją skutkuje natychmiastową oceną niedostateczną (2.0).

---

## 2. Aktorzy i role w systemie (RBAC)

1. **`TEAM_PRINCIPAL` (Szef Zespołu F1, np. Toto Wolff / Mercedes):**
   - Zgłasza oficjalne protesty incydentów wyścigowych.
   - Wgrywa materiał dowodowy (telemetria / zrzut ekranu).
   - Przegląda wyłącznie sprawy dotyczące własnego zespołu (zgłaszający lub oskarżony) oraz tabelę Superlicencji.
   - Brak uprawnień do zmiany statusów i nakładania kar.
2. **`FIA_STEWARD` (Sędzia Zawodów FIA):**
   - Pełny wgląd we wszystkie incydenty w toku.
   - Zmiana statusu sprawy (`REPORTED` $\to$ `UNDER_INVESTIGATION` $\to$ `NO_FURTHER_ACTION` / `PENALTY_ISSUED`).
   - Nakładanie oficjalnych sankcji (sekundy kary, punkty Superlicencji) z użyciem katalogu artykułów FIA.
   - Po orzeczeniu wyzwala automatyczną wysyłkę e-maila (PHPMailer / Mailpit) do ukaranego zespołu.
   - Brak możliwości składania protestów we własnym imieniu.
3. **`CRON_SYSTEM` (Autonomiczny proces w tle):**
   - Skrypt CLI `bin/cron.php expire-penalties`.
   - Analizuje punkty karne starsze niż 365 dni, oznacza je jako przedawnione (`is_expired = true`), aktualizuje stan zawieszenia kierowców i zapisuje audyt w logu.

---

## 3. Stack technologiczny

- **Backend:** PHP 8.3 (`declare(strict_types=1)`), autoloader PSR-4, czysty OOP MVC bez zewnętrznego frameworka, PDO z PostgreSQL, PHPMailer, PHPUnit.
- **Baza danych:** PostgreSQL 16 (transakcje ACID, widok `v_super_licence_standings`, klucze obce z ograniczeniami `ON DELETE RESTRICT/CASCADE`, normalizacja 3NF).
- **Frontend:** Semantyczny HTML5, nowoczesny CSS (Flexbox, Grid, zmienne CSS, dark/light mode bez FOUC), Vanilla JavaScript (moduły ES, Fetch API, asynchroniczna manipulacja DOM).
- **Infrastruktura:** Docker & Docker Compose (usługi: `server` Nginx, `php` PHP-FPM, `db` PostgreSQL 16, `mailpit` Mailpit, `pgadmin-wdpai`).

---

## 4. Struktura katalogów projektu

```text
.
├── bin/                       # Skrypty konsolowe (CRON CLI)
│   └── cron.php               # Skrypt wygaszania punktów karnych
├── config/                    # Konfiguracja środowiskowa i słowniki
│   ├── database.php           # Fabryka połączenia PDO
│   └── lang/                  # Tłumaczenia i18n (pl.php, en.php)
├── db/                        # Skrypty bazodanowe
│   ├── schema.sql             # Schemat tabel, relacji i widoków
│   └── seed.sql               # Dane demonstracyjne (zespoły, kierowcy, konta)
├── docs/                      # Dokumentacja projektu i wytyczne
│   ├── agents/                # Dedykowane wytyczne dla agentów AI
│   │   ├── backend.md         # Architektura obiektowa, serwisy, repozytoria
│   │   ├── frontend.md        # HTML5, CSS, JS, dark mode, WCAG
│   │   ├── database.md        # PostgreSQL, 3NF, transakcje, widoki
│   │   └── infrastructure.md  # Docker, Nginx, CRON, Mailpit, deploy
│   └── screenshots/           # Zrzuty ekranów do README (mobile + web)
├── docker/                    # Konfiguracja kontenerów
│   ├── db/                    # Dockerfile i skrypty inicjalizacyjne bazy
│   ├── nginx/                 # Konfiguracja Nginx (reverse proxy, static)
│   └── php/                   # Obraz PHP 8.3 FPM, rozszerzenia, Composer
├── public/                    # Jedyny publicznie dostępny katalog (Document Root)
│   ├── css/                   # Arkusze CSS (system stylów, dark mode)
│   ├── js/                    # Moduły JavaScript (theme.js, validator.js, api.js)
│   ├── uploads/               # Bezpieczny katalog na dowody (zablokowany exec PHP)
│   ├── views/                 # Szablony HTML (layout, partials, podstrony)
│   └── index.php              # Front Controller aplikacji
├── src/                       # Kod źródłowy backendu (PSR-4 App\)
│   ├── Controller/            # Kontrolery HTTP (cienkie, wywołują serwisy)
│   ├── Service/               # Logika biznesowa i domenowa
│   ├── Repository/            # Warstwa dostępu do danych (interfejsy i PDO)
│   ├── Model/                 # Encje domenowe i DTO
│   ├── Middleware/            # Filtry żądań (AuthMiddleware, RoleMiddleware)
│   └── Routing.php            # Router dopasowujący wzorce URL i metody HTTP
├── storage/                   # Logi i pliki tymczasowe
│   └── logs/                  # cron.log, app.log
├── tests/                     # Testy automatyczne (PHPUnit)
│   └── Unit/                  # Testy logiki biznesowej i transakcji
├── .env.example               # Wzór zmiennych środowiskowych bez sekretów
├── composer.json              # Autoloading PSR-4 i zależności
├── docker-compose.yaml        # Definicja usług Docker
├── kryteria.md                # Tabela 36 kryteriów oceny projektu
└── README.md                  # Oficjalna dokumentacja projektu
```

---

## 5. Dedykowane przewodniki dla agentów (Struktura modułowa)

Ze względu na rozbudowane wymagania techniczne i kryteria oceny, każdy obszar posiada własną instrukcję postępowania. Jako agent **musisz przestrzegać zasad z odpowiedniego pliku:**

- **Backend:** przeczytaj [docs/agents/backend.md](file:///c:/Users/dawid/PLIKI/Studia/sem_5/wdpai/wdpai-project/docs/agents/backend.md)  
  *(Architektura MVC, Controller-Service-Repository, transakcje ACID, walidacja serwerowa, sesja, RBAC, obsługa uploadu, PHPMailer, testy jednostkowe).*
- **Frontend:** przeczytaj [docs/agents/frontend.md](file:///c:/Users/dawid/PLIKI/Studia/sem_5/wdpai/wdpai-project/docs/agents/frontend.md)  
  *(Semantyczny HTML5, szablony layout/partials, Design System CSS, Dark/Light mode bez FOUC, Vanilla JS, Fetch API, standard WCAG 2.1 AA, przełącznik i18n).*
- **Baza Danych:** przeczytaj [docs/agents/database.md](file:///c:/Users/dawid/PLIKI/Studia/sem_5/wdpai/wdpai-project/docs/agents/database.md)  
  *(Normalizacja 3NF, klucze obce ON DELETE RESTRICT/CASCADE, widok `v_super_licence_standings`, skrypty seed i init.sql, diagram Mermaid ERD).*
- **Infrastruktura:** przeczytaj [docs/agents/infrastructure.md](file:///c:/Users/dawid/PLIKI/Studia/sem_5/wdpai/wdpai-project/docs/agents/infrastructure.md)  
  *(Docker Compose, Nginx, PHP-FPM, Mailpit, harmonogram zadań CRON w CLI, zmienne `.env`, procedura uruchomienia na czystym środowisku, Deploy).*

---

## 6. Złote zasady jakości i bezpieczeństwa (Security Bingo)

1. **SQL Injection:** Wyłącznie zapytania parametryzowane (`$stmt->prepare()` + `$stmt->execute()`). Zakaz konkatenacji zmiennych do stringów SQL.
2. **XSS (Cross-Site Scripting):** Każda zmienna renderowana w widokach musi być zabezpieczona:
   ```php
   <?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>
   ```
3. **CSRF (Cross-Site Request Forgery):** Każdy formularz POST oraz żądanie Fetch POST musi przesyłać token CSRF weryfikowany w sesji.
4. **Hasła:** Generowane przez `password_hash($pass, PASSWORD_ARGON2ID)` lub `PASSWORD_BCRYPT`. Porównywane wyłącznie przez `password_verify()`.
5. **Ciasteczka i sesja:** Flagi `HttpOnly`, `SameSite=Lax`, `Secure` (na produkcji), `session_regenerate_id(true)` po zalogowaniu.
6. **Upload plików:** Walidacja rzeczywistego MIME (`finfo`), ograniczenie do 5 MB, rozszerzenia jpg/jpeg/png, kryptograficznie losowa nazwa pliku (`random_bytes(16)`), zablokowane wykonywanie skryptów PHP w katalogu uploadów.
7. **Brak logiki w kontrolerach i widokach:** Kontroler tylko deleguje zadania do serwisów; widoki zawierają wyłącznie pętle `foreach` i warunki `if`.

---

## 7. Procedura weryfikacji zmian przed commitem

Przed zaraportowaniem zakończenia zadania lub commitem:
1. Sprawdź składnię wszystkich zmienionych plików PHP:
   ```bash
   docker compose exec php php -l <sciezka_do_pliku>
   ```
2. Zaktualizuj autoloader po dodaniu/przeniesieniu klas:
   ```bash
   docker compose exec php composer dump-autoload
   ```
3. Uruchom testy jednostkowe:
   ```bash
   docker compose exec php vendor/bin/phpunit
   ```
4. Sprawdź logi kontenerów pod kątem warningów i błędów:
   ```bash
   docker compose logs --tail=50 php server db
   ```
5. Przeprowadź weryfikację w przeglądarce pod kątem błędów w konsoli (F12) i efektu FOUC.
6. Sprawdź `git status` — zachowaj tylko celowe zmiany zgodne z konwencją commitów (`feat`, `fix`, `docs`, `refactor`, `test`, `chore`).
