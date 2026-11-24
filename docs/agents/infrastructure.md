# Wytyczne: Infrastruktura i DevOps

**Agent:** `infrastructure` · **Model: Opus 5.5** (konfiguracja środowiska, sekrety i bezpieczeństwo serwera: błąd tutaj blokuje wszystkie kryteria).
**Kryteria:** K2 (Docker), K32 (harmonogram CRON), K35 (`.env`), K36 (deploy). Wspiera K27 (nagłówki, uploady) i K12 (import bazy).
**Czytaj najpierw:** `AGENTS.md` §§ 5, 7, 8; karty Trello S0-02, S0-33, S0-37, S6-25, S8-31, S8-32.

---

## 1. Cel i zasady

1. `docker compose up --build -d` na czystym klonie, po `cp .env.example .env`, daje działającą aplikację z bazą i seedem. Zero ręcznych kroków.
2. Zero sekretów w repozytorium: ani w kodzie, ani w `Dockerfile`, ani w wartościach domyślnych compose.
3. Kontenery rozmawiają po nazwach usług (`db`, `mailpit`, `php`), dane bazy są w nazwanym wolumenie.
4. Każda zmiana w tym obszarze kończy się testem „czysty klon” (§ 7).

---

## 2. Usługi (`docker-compose.yaml`)

| Usługa | Obraz | Rola | Uwagi |
|---|---|---|---|
| `server` | Nginx (stable-alpine) | HTTP, pliki statyczne, FastCGI do `php:9000` | konfiguracja w § 3 |
| `php` | `php:8.3-fpm-alpine` | aplikacja | `pdo_pgsql`, `gd`, `zip`, `opcache`, `bcmath`, Composer 2, entrypoint z § 4 |
| `db` | `postgres:16-alpine` | PostgreSQL | wolumen `pgdata`, healthcheck, init z `./db` |
| `mailpit` | `axllent/mailpit` | SMTP `1025` + UI `8025` | tylko dev; w produkcji prawdziwy SMTP |
| `pgadmin-wdpai` | `dpage/pgadmin4` | GUI bazy | tylko dev, profil `dev` |
| `cron` (nowa) | ten sam obraz co `php` | uruchamia `bin/cron.php` wg harmonogramu | § 5 |

Wymagane poprawki względem obecnego stanu:
- **Healthcheck bazy** i `depends_on: condition: service_healthy` dla `php` i `cron` (aplikacja nie startuje przed gotową bazą):
  ```yaml
  db:
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U $${POSTGRES_USER} -d $${POSTGRES_DB}"]
      interval: 5s
      timeout: 3s
      retries: 10
  ```
- **Brak domyślnych haseł:** zmienne wymagane składnią `${DB_PASSWORD:?ustaw DB_PASSWORD w .env}`; usunąć `ENV POSTGRES_*` z `docker/db/Dockerfile` (karta S0-37). Zwykły `postgres:16-alpine` wystarczy, własny Dockerfile bazy jest zbędny.
- **Sieci:** `app-network` (server, php, cron) i `pg-network` (php, cron, db, mailpit, pgadmin). `server` nie widzi bazy.
- **Wolumeny:** `pgdata` (baza), `uploads` (jeśli uploady mają przetrwać `down`; mapowany na `public/uploads`), `vendor` (patrz § 4).
- **`pgadmin` i `mailpit`** w profilu `dev` (`profiles: ["dev"]`) i wystawione tylko lokalnie; w produkcji nie są uruchamiane.

---

## 3. Nginx (`docker/nginx/nginx.conf`)

Wymagania bezpieczeństwa (K27) i funkcjonalne:

```nginx
server {
    listen 80;
    server_name _;
    root /app/public;
    index index.php;
    client_max_body_size 6m;               # upload 5 MB + narzut multipart

    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header Referrer-Policy "same-origin" always;
    # CSP dostosuj po powstaniu stylów/JS (brak inline poza skryptem anty-FOUC: użyj hasha lub nonce):
    # add_header Content-Security-Policy "default-src 'self'; img-src 'self' data:; frame-ancestors 'none'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Do PHP-FPM trafia WYŁĄCZNIE front controller.
    location = /index.php {
        include fastcgi_params;
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    }

    # Każdy inny plik .php (w tym szablony *.html.php) = 404, nigdy wykonanie.
    location ~ \.php$ { return 404; }

    # Uploady: tylko statyczne, bez wykonywania czegokolwiek.
    location ^~ /uploads/ {
        default_type application/octet-stream;
        types { image/jpeg jpg jpeg; image/png png; }
        add_header X-Content-Type-Options "nosniff" always;
        try_files $uri =404;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```

Dlaczego to ważne: szablony leżą w `public/views/*.html.php`. Bez reguły „tylko `/index.php`” żądanie `GET /views/layout.html.php` wykonałoby szablon bezpośrednio (niezdefiniowane zmienne, ujawnione ścieżki). Po wdrożeniu reguły sprawdź: `curl -i localhost:8080/views/layout.html.php` → 404.

---

## 4. Obraz PHP i entrypoint (karta S0-33)

Problem: `COPY . .` nie instaluje zależności, a bind-mount `.:/app` przykrywa zawartość obrazu, więc `vendor/` nie istnieje i `index.php` spada na kod legacy.

Rozwiązanie:
1. W `docker/php/entrypoint.sh` (uruchamiany przed `php-fpm`):
   ```sh
   #!/bin/sh
   set -e
   cd /app
   mkdir -p storage/logs public/uploads
   chown -R www-data:www-data storage public/uploads
   if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
       composer install --no-interaction --prefer-dist --optimize-autoloader
   fi
   exec "$@"
   ```
2. W `Dockerfile`: `COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh`, `ENTRYPOINT ["entrypoint.sh"]`, `CMD ["php-fpm"]`.
3. W compose: nazwany wolumen `vendor:/app/vendor`, żeby zależności nie lądowały na dysku hosta (szybsze na Windows).
4. `composer.lock` w repozytorium (powtarzalność).
5. `php.ini`: `upload_max_filesize=5M`, `post_max_size=8M`, `memory_limit=256M`, `expose_php=Off`, `display_errors=Off` w produkcji (logi do stderr), `session.cookie_httponly=1`, `session.use_strict_mode=1`. Dla `APP_ENV=local` `display_errors` może być włączone.
6. Opcache: w dev `opcache.validate_timestamps=1`, w produkcji `0`.

---

## 5. CRON (K32)

Mechanizm: osobna usługa `cron` z obrazu `php`, uruchamiająca busybox `crond` w pierwszym planie.

```yaml
cron:
  build: { context: ., dockerfile: docker/php/Dockerfile }
  command: ["sh", "-c", "env | grep -E '^(DB_|APP_|SMTP_)' | sed \"s/^\\([^=]*\\)=\\(.*\\)$/export \\1='\\2'/\" > /etc/cron.env && exec crond -f -l 8"]
  volumes:
    - .:/app
    - ./docker/cron/crontab:/etc/crontabs/root:ro
  depends_on: { db: { condition: service_healthy } }
  networks: [app-network, pg-network]
```

`docker/cron/crontab` (crond nie dziedziczy zmiennych środowiskowych kontenera, stąd `cron.env`):
```text
0 0 * * * . /etc/cron.env; /usr/local/bin/php /app/bin/cron.php expire-penalties >> /app/storage/logs/cron.log 2>&1
```

Wymagania dla skryptu (implementuje `backend`, Ty zapewniasz uruchomienie):
- Idempotentność: powtórne uruchomienie nic nie psuje (`is_expired` ustawiane tylko dla `is_expired = false AND expires_at <= NOW()`).
- Wpis w logu z czasem UTC i liczbą przedawnionych kar i punktów; kod wyjścia ≠ 0 przy błędzie.
- Ręczne wywołanie do demo: `docker compose exec php php bin/cron.php expire-penalties`.
- Seed zawiera karę po terminie (`expires_at` w przeszłości, `is_expired = false`), żeby demo CRON miało co przedawnić (patrz `database.md`).

---

## 6. Konfiguracja i sekrety (K35)

`.env` w `.gitignore`, `.env.example` w repo z samymi wzorcami:

```env
APP_NAME=ApexSteward
APP_ENV=local            # local | production
APP_PORT=8080
APP_URL=http://localhost:8080

DB_HOST=db
DB_PORT=5432
DB_NAME=apex_steward
DB_USER=apex
DB_PASSWORD=change_me
POSTGRES_PORT=5432

SMTP_HOST=mailpit
SMTP_PORT=1025
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=
SMTP_FROM=stewards@apexsteward.local
MAILPIT_PORT=8025

PGADMIN_PORT=5050
PGADMIN_DEFAULT_EMAIL=admin@example.com
PGADMIN_DEFAULT_PASSWORD=change_me
```

Zasady: README nie zawiera prawdziwych haseł (poza hasłami kont demo aplikacji); `git grep -inE "password|secret|token"` po każdej zmianie konfiguracji; zmienne produkcyjne tylko w panelu hostingu lub menedżerze sekretów.

---

## 7. Test „czysty klon” (K2, przed każdym PR dotykającym infrastruktury)

```bash
git clone <repo> clean-test && cd clean-test
cp .env.example .env            # i uzupełnij DB_PASSWORD
docker compose up --build -d
docker compose ps               # wszystkie usługi healthy/running
docker compose logs --tail=50 php db server cron
curl -i http://localhost:8080/login                  # 200
curl -i http://localhost:8080/views/layout.html.php  # 404
docker compose exec php php bin/cron.php expire-penalties
docker compose down -v && docker compose up -d       # baza odtwarza się z seeda
```

Pamiętaj: skrypty z `./db` wykonują się tylko na pustym wolumenie; po zmianie schematu użyj `docker compose down -v`. Pliki są ładowane alfabetycznie (`schema.sql` przed `seed.sql`); usuń placeholder `db/init.sql`.

---

## 8. Deploy (K36)

Wymagania: publiczny URL, wersja z `main`, produkcyjna baza z importem schematu i seeda, zmienne środowiskowe w panelu, trwały dysk na `public/uploads`, HTTPS, link i krótki opis w README.

Wybór platformy (sprawdź aktualne limity darmowych planów przed decyzją):
- **VPS + Docker Compose** (rekomendowane: ta sama konfiguracja co lokalnie). Dodaj `docker-compose.prod.yaml` (bez bind-mountów, bez `pgadmin` i `mailpit`, `restart: unless-stopped`, `APP_ENV=production`) oraz reverse proxy z automatycznym HTTPS (np. Caddy).
- **PaaS (Render, Railway, Fly.io):** zwykle jeden kontener na usługę, więc potrzebny `Dockerfile.prod` z Nginx i PHP-FPM w jednym obrazie (supervisord) oraz zarządzany Postgres i wolumen na uploady.

Obowiązkowe po wdrożeniu: logowanie na oba konta demo, pełny przepływ protestu i orzeczenia, e-mail (prawdziwy SMTP lub środowisko testowe), uruchomienie CRON, ciasteczka z flagą `Secure`.
Opcjonalnie: GitHub Actions (`php -l`, `phpunit`, `docker compose config`) jako dowód jakości do K7/K31.

---

## 9. Definicja ukończenia i czego nie robić

Gotowe, gdy: test z § 7 przechodzi bez ręcznych poprawek, `git grep` nie znajduje sekretów, nagłówki i reguły Nginx działają, CRON uruchamia się ręcznie i z harmonogramu.
Nie rób: nie commituj `.env`, nie zostawiaj domyślnych haseł „na chwilę”, nie wystawiaj pgAdmin/Mailpit w produkcji, nie zmieniaj schematu bazy ani kodu aplikacji (od tego są `database` i `backend`).
