# AGENTS.md

Ten plik jest przewodnikiem dla agentow AI pracujacych w tym repozytorium. Przed wprowadzeniem zmian przeczytaj go razem z `README.md` i sprawdz aktualny stan kodu. Nie zakladaj, ze opisana funkcja istnieje, dopoki nie potwierdzisz tego w plikach projektu.

## Cel projektu

To edukacyjna aplikacja webowa napisana w czystym PHP 8.3. Korzysta z lekkiej, samodzielnie zaimplementowanej architektury MVC, bez frameworka PHP. Aplikacja dziala w kontenerach Nginx i PHP-FPM uruchamianych przez Docker Compose.

Aktualnie aplikacja obsluguje:

- strone glowna pod `/`,
- liste projektow pod `/projects`,
- szczegoly projektu pod `/projects/{id}`,
- odpowiedz 404 dla nieznanych tras.

## Stack technologiczny

- PHP 8.3 z `declare(strict_types=1)`
- Composer i autoloading PSR-4
- Nginx jako serwer HTTP
- PHP-FPM do wykonywania kodu PHP
- Docker oraz Docker Compose
- HTML i CSS bez frameworka frontendowego

## Struktura katalogow

```text
.
├── docker/
│   ├── nginx/                 # obraz i konfiguracja Nginx
│   └── php/                   # obraz PHP-FPM i rozszerzenia PHP
├── public/                    # jedyny publicznie dostepny katalog
│   ├── css/style.css          # globalne style
│   ├── index.php              # front controller i definicje tras
│   └── views/                 # szablony HTML renderowane przez kontrolery
├── src/
│   ├── controllers/           # kontrolery aplikacji
│   └── Routing.php            # rejestracja i obsluga tras
├── composer.json              # konfiguracja autoloadingu
└── docker-compose.yaml        # uslugi `server` i `php`
```

Nie umieszczaj logiki aplikacji ani danych wrazliwych w `public/`. Ten katalog jest document rootem Nginx i sluzy tylko jako punkt wejscia oraz miejsce na widoki i zasoby publiczne.

## Architektura MVC

Projekt stosuje uproszczony przeplyw MVC:

```text
przegladarka
    -> Nginx
    -> public/index.php
    -> Routing
    -> Controller
    -> Model / serwis (gdy zostanie dodany)
    -> View
    -> odpowiedz HTML
```

### Front Controller

`public/index.php` jest jedynym punktem wejscia aplikacji. Laduje autoloader Composera, rejestruje trasy, przekazuje metode HTTP i URI do routera oraz uruchamia widok 404, gdy zadna trasa nie pasuje.

Nowe trasy dodawaj w tym pliku w postaci:

```php
$routing->get('/example', ExampleController::class, 'index');
$routing->post('/example', ExampleController::class, 'store');
```

### Router

`src/Routing.php` laczy metode HTTP i sciezke URL z publiczna metoda kontrolera. Parametry zapisane jako `{name}` sa obecnie dopasowywane tylko do cyfr, konwertowane na `int` i przekazywane do akcji kontrolera przez nazwane argumenty.

Przyklad: `/projects/{id}` wywoluje `ProjectController::show(int $id)`.

Router obsluguje obecnie metody GET i POST. Nie dodawaj logiki biznesowej do routera.

### Controller

Kontrolery znajduja sie w `src/controllers/`, uzywaja przestrzeni nazw `App\Controller` i dziedzicza po `AppController`. Ich zadaniem jest:

- odebranie i walidacja danych wejsciowych,
- wywolanie modelu lub serwisu,
- przygotowanie danych dla widoku,
- wybranie szablonu przez `$this->render()`.

Kontroler powinien byc cienki. Nie umieszczaj w nim rozbudowanych zapytan do bazy, HTML ani stylow.

### Model

Warstwa Model nie jest jeszcze zaimplementowana. Nie udawaj, ze dane widoczne w widokach pochodza z bazy danych. Gdy funkcja wymaga trwalego przechowywania danych, wydziel klasy modelu/repozytorium zamiast dodawac zapytania SQL do kontrolera.

Dla nowych klas zgodnych z mapowaniem `App\` w Composerze preferuj katalogi i nazwy klas o zgodnej wielkosci liter, np.:

```text
src/Model/Project.php          -> App\Model\Project
src/Repository/ProjectRepository.php -> App\Repository\ProjectRepository
```

Po dodaniu lub przeniesieniu klas odswiez autoloader poleceniem `composer dump-autoload`.

### View

Widoki znajduja sie w `public/views/` i maja nazwy `*.html.php`. `AppController::render()` przekazuje do nich dane jako zmienne przy uzyciu `extract(..., EXTR_SKIP)`.

Widoki odpowiadaja za prezentacje. Dozwolone sa w nich proste instrukcje warunkowe i petle, ale nie logika biznesowa, dostep do bazy ani obsluga routingu.

Kazda wartosc pochodzaca od uzytkownika lub z bazy musi byc escapowana w kontekscie HTML:

```php
<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>
```

## Konwencje implementacji

- Zachowuj `declare(strict_types=1)` we wszystkich nowych plikach PHP.
- Uzywaj deklaracji typow parametrow, zwracanych wartosci i wlasciwosci.
- Nazwy klas zapisuj w `PascalCase`, a metod i zmiennych w `camelCase`.
- Nazwy techniczne w kodzie zapisuj po angielsku; tekst interfejsu moze byc po polsku.
- Jeden kontroler powinien obslugiwac jeden spojny obszar aplikacji.
- Dane przekazuj do widoku jawnie w tablicy drugiego argumentu `render()`.
- Nie tworz globalnego stanu i nie uzywaj `$_GET`, `$_POST` ani `$_SERVER` poza warstwa wejscia, jezeli dane mozna przekazac jako argument.
- Nie dodawaj frameworka ani duzej biblioteki bez wyraznej potrzeby i zgody uzytkownika.
- Sekrety umieszczaj w `.env`; nigdy nie commituj hasel, tokenow ani danych dostepowych.
- Nie edytuj `vendor/` recznie.
- Zachowuj zakres zadania i nie zmieniaj niezwiązanych plikow.

## Dodawanie nowej funkcji

Typowy porzadek prac:

1. Dodaj trase w `public/index.php`.
2. Dodaj albo rozszerz kontroler w `src/controllers/`.
3. Dodaj model, repozytorium lub serwis, jesli funkcja zawiera logike biznesowa albo dostep do danych.
4. Dodaj widok w `public/views/` i przekaz do niego tylko potrzebne dane.
5. Dodaj lub zmien style w `public/css/style.css`.
6. Sprawdz poprawna trase, dane wejsciowe, HTML oraz zachowanie dla bledow i 404.

## Uruchamianie projektu

```bash
docker compose up --build -d
```

Aplikacja jest domyslnie dostepna pod `http://localhost:8081`. Port mozna zmienic przez zmienna `APP_PORT`.

Przydatne polecenia:

```bash
docker compose ps
docker compose logs -f
docker compose exec php php -l src/Routing.php
docker compose exec php composer dump-autoload
docker compose down
```

Jesli katalog `vendor/` nie istnieje, zainstaluj zaleznosci/autoloader poleceniem:

```bash
docker compose run --rm php composer install
```

## Weryfikacja zmian

W projekcie nie ma obecnie skonfigurowanego zestawu testow automatycznych ani narzedzia do formatowania. Po zmianie:

- uruchom `php -l` dla kazdego zmienionego pliku PHP,
- sprawdz `composer dump-autoload`, jezeli zmieniales klasy lub namespace,
- uruchom kontenery i sprawdz zmienione endpointy,
- sprawdz przypadek poprawny, bledne dane oraz nieistniejaca trase,
- upewnij sie, ze logi `docker compose logs` nie zawieraja nowych bledow,
- nie twierdz, ze testy przeszly, jezeli ich faktycznie nie uruchomiono.

## Zasady dla agentow AI

- Najpierw przeczytaj pliki bezposrednio zwiazane z zadaniem i potwierdz aktualna implementacje.
- Przed edycja sprawdz `git status`; zachowaj zmiany uzytkownika i nie cofaj ich.
- Wprowadzaj najmniejsza spojna zmiane realizujaca wymaganie.
- Utrzymuj rozdzial odpowiedzialnosci MVC.
- Nie rozszerzaj zakresu zadania o refaktoryzacje, migracje lub nowe zaleznosci bez uzasadnienia.
- Jesli wymaganie jest niejednoznaczne, wybierz rozwiazanie zgodne z istniejacymi wzorcami; pytaj tylko wtedy, gdy wybor istotnie zmieni zachowanie aplikacji.
- Po zakonczeniu opisz zmienione pliki oraz wykonana weryfikacje.
