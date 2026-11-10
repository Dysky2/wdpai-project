# ApexSteward (FIA Stewards Hub)

System zarządzania incydentami wyścigowymi i orzecznictwem sędziowskim FIA w Formule 1.

Platforma umożliwia szefom zespołów F1 (`TEAM_PRINCIPAL`) oficjalne zgłaszanie protestów z materiałem dowodowym (onboard / telemetria), sędziom FIA (`FIA_STEWARD`) pełne procedowanie spraw z katalogiem kar i atomowym naliczaniem punktów Superlicencji, a procesom autonomicznym (`CRON_SYSTEM`) cykliczne wygaszanie punktów karnych starszych niż 365 dni.

---

## Tech stack

| Warstwa | Technologie |
|---|---|
| **Backend** | PHP 8.3 (czyste programowanie obiektowe, MVC, PSR-4/12, PDO) |
| **Baza danych** | PostgreSQL 16 (transakcje ACID, widoki, 3NF) |
| **Frontend** | Semantyczny HTML5, nowoczesny CSS (zmienne, dark/light mode), Vanilla JS |
| **Serwer HTTP** | Nginx (reverse proxy, static files, upload protection) |
| **Poczta dev** | Mailpit (SMTP server + Web UI) |
| **Konteneryzacja** | Docker & Docker Compose |

---

## Usługi Docker i mapowanie portów

Projekt uruchamia 5 współpracujących usług w dedykowanych sieciach Docker:

| Usługa | Obraz / Dockerfile | Port zewnętrzny (Host) | Port wewnętrzny (Kontener) | Opis |
|---|---|---|---|---|
| **`server`** | `docker/nginx/Dockerfile` | `8080` (`APP_PORT`) | `80` | Serwer HTTP Nginx, punkt wejścia do aplikacji |
| **`php`** | `docker/php/Dockerfile` | - | `9000` | Interpreter PHP 8.3-FPM z rozszerzeniami PDO PgSQL |
| **`db`** | `docker/db/Dockerfile` (Postgres 16) | `5432` (`POSTGRES_PORT`) | `5432` | Baza danych PostgreSQL z wolumenem `pgdata` |
| **`mailpit`** | `axllent/mailpit:latest` | `8025` (`MAILPIT_PORT`)<br>`1025` (`SMTP_PORT`) | `8025`<br>`1025` | Web UI podglądu poczty oraz serwer SMTP |
| **`pgadmin-wdpai`** | `dpage/pgadmin4:latest` | `5050` (`PGADMIN_PORT`) | `80` | Graficzny interfejs administracji bazą PostgreSQL |

Komunikacja między kontenerami odbywa się **wyłącznie po nazwach usług** (`host=db`, `host=mailpit`, `host=php`).

---

## Zmienne środowiskowe (.env)

Konfiguracja aplikacji zarządzana jest przez zmienne środowiskowe. W repozytorium znajduje się bezpieczny szablon `.env.example`.

| Zmienna | Wartość domyślna | Opis |
|---|---|---|
| `APP_NAME` | `ApexSteward` | Nazwa aplikacji |
| `APP_ENV` | `local` | Środowisko uruchomieniowe (`local`, `production`) |
| `APP_PORT` | `8080` | Port HTTP serwera Nginx na hoście |
| `APP_URL` | `http://localhost:8080` | Pełny adres bazowy aplikacji |
| `DB_HOST` | `db` | Nazwa usługi bazy danych w Dockerze |
| `DB_PORT` | `5432` | Port bazy danych wewnątrz sieci Dockera |
| `DB_NAME` | `apex_steward` | Nazwa bazy danych |
| `DB_USER` | `docker` | Użytkownik bazy danych |
| `DB_PASSWORD` | `docker` | Hasło użytkownika bazy danych |
| `POSTGRES_PORT`| `5432` | Port PostgreSQL wystawiony na maszynę hosta |
| `SMTP_HOST` | `mailpit` | Nazwa usługi serwera SMTP dla PHPMailer |
| `SMTP_PORT` | `1025` | Port SMTP serwera Mailpit |
| `MAILPIT_PORT` | `8025` | Port Web UI skrzynki Mailpit na hoście |
| `PGADMIN_PORT` | `5050` | Port interfejsu PgAdmin na hoście |

---

## Szybki start (Czyste środowisko)

```bash
# 1. Sklonuj repozytorium
git clone https://github.com/Dysky2/wdpai-project.git
cd wdpai-project

# 2. Utwórz plik konfiguracji lokalnej
cp .env.example .env

# 3. Uruchom kontenery przez Docker Compose
docker compose up --build -d
```

### Dostęp do usług:
- **Aplikacja webowa:** [http://localhost:8080](http://localhost:8080)
- **Skrzynka pocztowa (Mailpit):** [http://localhost:8025](http://localhost:8025)
- **PgAdmin:** [http://localhost:5050](http://localhost:5050) (login: `admin@wdpai.pl`, hasło: `admin`)

---

## Zasady developmentu

- Branch dla każdego zadania (konwencja `WDP-XX`).
- Małe, logiczne commity zgodne z konwencją (`feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`).
- Sekrety wyłącznie w `.env` (plik `.env` bezwzględnie w `.gitignore`).
