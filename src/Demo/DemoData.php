<?php

declare(strict_types=1);

namespace App\Demo;

use App\Model\LicenceStatus;

/**
 * TEMPORARY presentation data taken from the UI prototypes (docs/ui).
 * It exists only so the screens can be shown before the database layer, services
 * and authentication exist. Controllers take this class through the constructor;
 * replace it with real services (IncidentService, StandingsService, session user) later.
 */
final class DemoData
{
    public const PER_PAGE = 6;
    public const STANDINGS_PER_PAGE = 10;
    public const TOTAL_LAPS = 78;

    private const STATUS_LABELS = [
        'REPORTED' => 'Zgłoszony',
        'UNDER_INVESTIGATION' => 'W trakcie dochodzenia',
        'PENALTY_ISSUED' => 'Kara nałożona',
        'NO_FURTHER_ACTION' => 'Bez dalszych działań',
    ];

    private const LICENCE_LABELS = [
        'SUSPENDED' => 'Zawieszony',
        'AT_BAN_RISK' => 'Ryzyko zawieszenia',
        'CAUTION' => 'Ostrzeżenie',
        'ACTIVE' => 'Aktywna',
        'CLEAN' => 'Czysta',
    ];

    /** code => [number, name, team, active points, days until the oldest points expire] */
    private const DRIVERS = [
        'MAG' => [20, 'Kevin Magnussen', 'MoneyGram Haas F1 Team', 10, 18],
        'PER' => [11, 'Sergio Pérez', 'Oracle Red Bull Racing', 7, 57],
        'STR' => [18, 'Lance Stroll', 'Aston Martin Aramco', 5, 116],
        'VER' => [1, 'Max Verstappen', 'Oracle Red Bull Racing', 4, 160],
        'TSU' => [22, 'Yuki Tsunoda', 'Visa Cash App RB', 3, 74],
        'RUS' => [63, 'George Russell', 'Mercedes-AMG Petronas', 2, 200],
        'HAM' => [44, 'Lewis Hamilton', 'Mercedes-AMG Petronas', 0, 0],
        'LEC' => [16, 'Charles Leclerc', 'Scuderia Ferrari', 0, 0],
        'NOR' => [4, 'Lando Norris', 'McLaren F1 Team', 0, 0],
        'PIA' => [81, 'Oscar Piastri', 'McLaren F1 Team', 0, 0],
        'SAI' => [55, 'Carlos Sainz', 'Scuderia Ferrari', 0, 0],
        'ALO' => [14, 'Fernando Alonso', 'Aston Martin Aramco', 0, 0],
        'ALB' => [23, 'Alexander Albon', 'Williams Racing', 0, 0],
        'GAS' => [10, 'Pierre Gasly', 'BWT Alpine F1 Team', 0, 0],
        'OCO' => [31, 'Esteban Ocon', 'BWT Alpine F1 Team', 0, 0],
        'BOT' => [77, 'Valtteri Bottas', 'Kick Sauber', 0, 0],
        'ZHO' => [24, 'Zhou Guanyu', 'Kick Sauber', 0, 0],
        'HUL' => [27, 'Nico Hülkenberg', 'MoneyGram Haas F1 Team', 0, 0],
        'RIC' => [3, 'Daniel Ricciardo', 'Visa Cash App RB', 0, 0],
        'SAR' => [2, 'Logan Sargeant', 'Williams Racing', 0, 0],
    ];

    /** id => [lap, driver codes, title, summary, status, penalty label] */
    private const INCIDENTS = [
        42 => [24, ['VER', 'HAM'], 'Zakręt 1: spowodowanie kolizji / wypchnięcie z toru', 'Analiza telemetrii i kamer onboard w strefie hamowania zakrętu 1', 'UNDER_INVESTIGATION', null],
        41 => [21, ['MAG'], 'Przekroczenie limitów toru: 4. powtórzenie (zakręt 10)', 'Automatyczne wykrycie potwierdzone przez pętlę pomiaru czasu', 'PENALTY_ISSUED', 'Kara +5 s'],
        40 => [18, ['SAI', 'PIA'], 'Przecięcie linii wyjazdu z alei serwisowej z utrudnieniem', 'Zgłoszone przez dyrektora wyścigu, oczekuje na powtórkę z kamer sektora', 'REPORTED', null],
        39 => [12, ['PER', 'ALB'], 'Lekki kontakt na nawrocie w 1. okrążeniu', 'Uznane za typowy incydent wyścigowy na starcie', 'NO_FURTHER_ACTION', null],
        38 => [8, ['STR'], 'Niebezpieczne wypuszczenie z boksu', 'Naruszenie art. 34.14: klucz pneumatyczny w torze jazdy', 'PENALTY_ISSUED', 'Stop & Go 10 s'],
        37 => [3, ['HUL', 'ZHO'], 'Miejsce na wierzchołku zakrętu 3 przy wyprzedzaniu', 'Pełna szerokość samochodu zachowana przez cały zakręt', 'NO_FURTHER_ACTION', null],
        36 => [31, ['ALO', 'RUS'], 'Obrona pozycji: spóźniony ruch przy hamowaniu', 'Zgłoszone przez zespół, oczekuje na materiał z kamer', 'REPORTED', null],
        35 => [28, ['NOR', 'LEC'], 'Kontakt przy wyprzedzaniu w zakręcie 8', 'Analiza materiału z kamery pokładowej i telemetrii', 'UNDER_INVESTIGATION', null],
        34 => [26, ['TSU'], 'Prędkość w alei serwisowej powyżej limitu', 'Pomiar 83 km/h przy limicie 80 km/h', 'PENALTY_ISSUED', 'Kara +5 s'],
        33 => [22, ['BOT', 'OCO'], 'Zmiana toru jazdy na prostej start-meta', 'Zgłoszone przez zespół, oczekuje na decyzję', 'REPORTED', null],
        32 => [17, ['GAS'], 'Zjazd poza tor w zakręcie 5', 'Brak przewagi, powrót na tor bez zagrożenia', 'NO_FURTHER_ACTION', null],
        31 => [9, ['RIC', 'SAR'], 'Kontakt kół w zakręcie 6', 'Typowy incydent wyścigowy, brak winnego', 'NO_FURTHER_ACTION', null],
    ];

    /** @return array{navItems: list<array{label: string, href: string, route: string}>, currentUser: array{roleLabel: string, name: string, initial: string}, activeRoute: string} */
    public function shell(string $activeRoute): array
    {
        return [
            'navItems' => [
                ['label' => 'Incydenty', 'href' => '/incidents', 'route' => '/incidents'],
                ['label' => 'Klasyfikacja', 'href' => '/standings', 'route' => '/standings'],
                ['label' => 'Nowy protest', 'href' => '/incidents/create', 'route' => '/incidents/create'],
            ],
            'currentUser' => ['roleLabel' => 'Sędzia FIA', 'name' => 'John D.', 'initial' => 'J'],
            'activeRoute' => $activeRoute,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function incidents(): array
    {
        $rows = [];
        foreach (self::INCIDENTS as $id => $incident) {
            $rows[] = $this->incidentRow($id, $incident);
        }

        return $rows;
    }

    /** @return array<string, int> */
    public function incidentCounts(): array
    {
        $counts = ['TOTAL' => 0, 'REPORTED' => 0, 'UNDER_INVESTIGATION' => 0, 'PENALTY_ISSUED' => 0, 'NO_FURTHER_ACTION' => 0];
        foreach (self::INCIDENTS as $incident) {
            $counts['TOTAL']++;
            $counts[$incident[4]]++;
        }
        $counts['CLOSED'] = $counts['PENALTY_ISSUED'] + $counts['NO_FURTHER_ACTION'];

        return $counts;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function filterIncidents(string $filter, string $query, int $page): array
    {
        $rows = array_values(array_filter(
            $this->incidents(),
            function (array $row) use ($filter, $query): bool {
                $inFilter = match ($filter) {
                    'reported' => $row['status'] === 'REPORTED',
                    'investigation' => $row['status'] === 'UNDER_INVESTIGATION',
                    'closed' => in_array($row['status'], ['PENALTY_ISSUED', 'NO_FURTHER_ACTION'], true),
                    default => true,
                };

                return $inFilter && $this->matches($query, [$row['code'], $row['title'], $row['summary'], ...array_column($row['drivers'], 'code'), ...array_column($row['drivers'], 'name')]);
            },
        ));

        return $this->paginate($rows, $page, self::PER_PAGE);
    }

    /** @return array<string, mixed>|null */
    public function incident(int $id): ?array
    {
        if (!isset(self::INCIDENTS[$id])) {
            return null;
        }

        $row = $this->incidentRow($id, self::INCIDENTS[$id]);
        $accused = $this->driver($row['drivers'][0]['code']);
        $affected = isset($row['drivers'][1]) ? $this->driver($row['drivers'][1]['code']) : null;

        return $row + [
            'reportedAt' => '26.05.2024, 15:38 UTC',
            'grandPrix' => 'GP Monako 2024',
            'circuit' => 'Circuit de Monaco',
            'totalLaps' => self::TOTAL_LAPS,
            'corner' => 'Zakręt 1 (Sainte Dévote)',
            'accused' => $accused,
            'affected' => $affected,
            'article' => 'Załącznik L, rozdz. IV, art. 2(b) oraz Przepisy Sportowe F1 art. 33.3',
            'lodgedBy' => 'Mercedes-AMG Petronas',
            'lodgedByPerson' => 'Ron Meadows (dyrektor sportowy)',
            'lodgedAt' => 'Zgłoszono o 15:39:04 UTC',
            'docRef' => 'DOC 47 / RACING-2024',
            'evidence' => [
                'caption' => 'Rys. 1: przód bolidu nr 44 i tył bolidu nr 1 nakładają się na wierzchołku zakrętu (15:38:12).',
                'file' => 'onboard_cam_turn1_ver_ham.jpg',
                'size' => '2,4 MB',
                'sha256' => '7f3c9a1d…4b8e',
            ],
            'penalty' => $row['status'] === 'PENALTY_ISSUED'
                ? ['type' => $row['penaltyLabel'], 'points' => 2, 'article' => 'Załącznik L, rozdz. IV, art. 2', 'reason' => $row['summary']]
                : null,
        ];
    }

    /**
     * @return array{kpis: array<string, mixed>, rows: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function standings(string $filter, string $query, int $page): array
    {
        $all = [];
        foreach (self::DRIVERS as $code => $driver) {
            $all[] = $this->standingRow($code, $driver);
        }
        usort($all, static fn (array $a, array $b): int => [$b['points'], $a['number']] <=> [$a['points'], $b['number']]);
        foreach ($all as $position => &$row) {
            $row['position'] = $position + 1;
        }
        unset($row);

        $top = $all[0];
        $atRisk = count(array_filter($all, static fn (array $r): bool => $r['points'] >= LicenceStatus::AT_RISK_THRESHOLD));
        $clean = count(array_filter($all, static fn (array $r): bool => $r['points'] === 0));
        $kpis = [
            'total' => count($all),
            'top' => $top,
            'toBan' => LicenceStatus::POINTS_LIMIT - $top['points'],
            'pending' => $this->incidentCounts()['UNDER_INVESTIGATION'],
            'clean' => $clean,
            'cleanPercent' => (int) round($clean / count($all) * 100),
            'atRisk' => $atRisk,
        ];

        $rows = array_values(array_filter(
            $all,
            fn (array $row): bool => match ($filter) {
                'risk' => $row['points'] >= LicenceStatus::AT_RISK_THRESHOLD,
                'clean' => $row['points'] === 0,
                default => true,
            } && $this->matches($query, [$row['name'], $row['team'], (string) $row['number'], $row['code']]),
        ));

        return ['kpis' => $kpis] + $this->paginate($rows, $page, self::STANDINGS_PER_PAGE);
    }

    /** @return array<string, mixed> */
    public function adjudicationForm(int $incidentId): ?array
    {
        $incident = $this->incident($incidentId);
        if ($incident === null) {
            return null;
        }

        return [
            'incident' => $incident,
            'currentPoints' => $incident['accused']['points'],
            'defaultPoints' => 2,
            'projectedPoints' => $incident['accused']['points'] + 2,
            'articles' => [
                'Załącznik L, rozdz. IV, art. 2: spowodowanie kolizji / wypchnięcie z toru',
                'Załącznik L, rozdz. IV, art. 3: przekroczenie limitów toru',
                'Załącznik L, rozdz. IV, art. 5: niebezpieczne wypuszczenie z boksu',
                'Przepisy Sportowe F1 art. 33.3: utrudnianie innego kierowcy',
            ],
            'sanctions' => [
                ['value' => 'TIME_PENALTY:5', 'title' => 'Kara 5 sekund', 'text' => 'Doliczana do czasu wyścigu lub odbywana w boksie'],
                ['value' => 'TIME_PENALTY:10', 'title' => 'Kara 10 sekund', 'text' => 'Surowa kara czasowa w trakcie wyścigu'],
                ['value' => 'GRID_DROP:3', 'title' => 'Cofnięcie o 3 miejsca', 'text' => 'Stosowane na starcie następnego wyścigu'],
                ['value' => 'DRIVE_THROUGH', 'title' => 'Przejazd przez aleję', 'text' => 'Wjazd do alei bez zatrzymania'],
                ['value' => 'REPRIMAND', 'title' => 'Formalna nagana', 'text' => 'Ostrzeżenie dyscyplinarne bez kary czasowej'],
                ['value' => 'DISQUALIFICATION', 'title' => 'Dyskwalifikacja (DSQ)', 'text' => 'Całkowite wykluczenie z klasyfikacji'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function reportForm(): array
    {
        $accused = [];
        foreach (self::DRIVERS as $code => $driver) {
            if ($driver[2] !== 'Mercedes-AMG Petronas') {
                $accused[] = ['value' => $code, 'label' => 'Bolid ' . $driver[0] . ': ' . $driver[1] . ' (' . $driver[2] . ')'];
            }
        }

        return [
            'grandPrix' => '2024 Grand Prix Monako: wyścig',
            'team' => 'Mercedes-AMG PETRONAS F1 Team',
            'teamLicence' => 'Licencja zespołu FIA #MERC-2024-GER',
            'representative' => 'Ron Meadows (dyrektor sportowy)',
            'totalLaps' => self::TOTAL_LAPS,
            'accusedDrivers' => $accused,
            'ownDrivers' => [
                ['value' => 'HAM', 'label' => 'Bolid 44: Lewis Hamilton'],
                ['value' => 'RUS', 'label' => 'Bolid 63: George Russell'],
            ],
            'articles' => [
                'Spowodowanie kolizji / wypchnięcie z toru (ISC Zał. L rozdz. IV)',
                'Przekroczenie limitów toru (ISC Zał. L rozdz. IV)',
                'Niebezpieczne wypuszczenie z boksu (ISC Zał. L rozdz. IV)',
            ],
        ];
    }

    /**
     * @param array{0: int, 1: list<string>, 2: string, 3: string, 4: string, 5: ?string} $incident
     * @return array<string, mixed>
     */
    private function incidentRow(int $id, array $incident): array
    {
        [$lap, $codes, $title, $summary, $status, $penalty] = $incident;

        return [
            'id' => $id,
            'code' => sprintf('INC-%03d', $id),
            'lap' => $lap,
            'drivers' => array_map(fn (string $code): array => ['number' => self::DRIVERS[$code][0], 'code' => $code, 'name' => self::DRIVERS[$code][1]], $codes),
            'title' => $title,
            'summary' => $summary,
            'status' => $status,
            'statusLabel' => self::STATUS_LABELS[$status],
            'penaltyLabel' => $penalty,
            'url' => '/incidents/' . $id,
        ];
    }

    /** @return array<string, mixed> */
    private function driver(string $code): array
    {
        [$number, $name, $team, $points] = self::DRIVERS[$code];

        return ['code' => $code, 'number' => $number, 'name' => $name, 'team' => $team, 'points' => $points];
    }

    /**
     * @param array{0: int, 1: string, 2: string, 3: int, 4: int} $driver
     * @return array<string, mixed>
     */
    private function standingRow(string $code, array $driver): array
    {
        [$number, $name, $team, $points, $daysToExpiry] = $driver;
        $status = LicenceStatus::fromPoints($points);
        $expiry = $daysToExpiry > 0 ? new \DateTimeImmutable('+' . $daysToExpiry . ' days') : null;

        return [
            'code' => $code,
            'number' => $number,
            'name' => $name,
            'team' => $team,
            'points' => $points,
            'percent' => (int) round($points / LicenceStatus::POINTS_LIMIT * 100),
            'expiry' => $expiry?->format('d.m.Y'),
            'daysRemaining' => $daysToExpiry > 0 ? $daysToExpiry : null,
            'status' => $status->value,
            'statusLabel' => self::LICENCE_LABELS[$status->value],
        ];
    }

    /** @param list<string> $haystack */
    private function matches(string $query, array $haystack): bool
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return true;
        }
        foreach ($haystack as $value) {
            if (str_contains(mb_strtolower($value), $query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    private function paginate(array $rows, int $page, int $perPage): array
    {
        $total = count($rows);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        return [
            'rows' => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }
}
