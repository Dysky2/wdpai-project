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
    public const STANDINGS_PER_PAGE = 10;
    public const TOTAL_LAPS = 78;

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
