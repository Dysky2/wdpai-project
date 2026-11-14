-- ApexSteward - dane demonstracyjne (ladowane po schema.sql, tylko na pustym wolumenie).
-- Daty kar sa liczone wzgledem now(), wiec demo dziala niezaleznie od dnia uruchomienia.
-- Konta demo: toto@mercedes.f1 (TEAM_PRINCIPAL), steward@fia.org (FIA_STEWARD); jawne haslo tylko w README.
-- Pozostale konta principal@<zespol>.f1 maja to samo haslo demo i sluza do pokazania zakresu widocznosci spraw.

INSERT INTO teams (name, license_country) VALUES
    ('Oracle Red Bull Racing',  'GBR'),
    ('Mercedes-AMG Petronas',   'GER'),
    ('Scuderia Ferrari',        'ITA'),
    ('McLaren F1 Team',         'GBR'),
    ('Aston Martin Aramco',     'GBR'),
    ('BWT Alpine F1 Team',      'FRA'),
    ('Williams Racing',         'GBR'),
    ('Visa Cash App RB',        'ITA'),
    ('Kick Sauber',             'SUI'),
    ('MoneyGram Haas F1 Team',  'USA');

INSERT INTO drivers (team_id, first_name, last_name, code, race_number, is_suspended)
SELECT t.id, v.first_name, v.last_name, v.code, v.race_number, v.is_suspended
FROM (VALUES
    ('Oracle Red Bull Racing', 'Max',       'Verstappen', 'VER',  1, FALSE),
    ('Oracle Red Bull Racing', 'Sergio',    'Pérez',      'PER', 11, FALSE),
    ('Mercedes-AMG Petronas',  'Lewis',     'Hamilton',   'HAM', 44, FALSE),
    ('Mercedes-AMG Petronas',  'George',    'Russell',    'RUS', 63, FALSE),
    ('Scuderia Ferrari',       'Charles',   'Leclerc',    'LEC', 16, FALSE),
    ('Scuderia Ferrari',       'Carlos',    'Sainz',      'SAI', 55, FALSE),
    ('McLaren F1 Team',        'Lando',     'Norris',     'NOR',  4, FALSE),
    ('McLaren F1 Team',        'Oscar',     'Piastri',    'PIA', 81, FALSE),
    ('Aston Martin Aramco',    'Fernando',  'Alonso',     'ALO', 14, FALSE),
    ('Aston Martin Aramco',    'Lance',     'Stroll',     'STR', 18, FALSE),
    ('BWT Alpine F1 Team',     'Pierre',    'Gasly',      'GAS', 10, FALSE),
    ('BWT Alpine F1 Team',     'Esteban',   'Ocon',       'OCO', 31, FALSE),
    ('Williams Racing',        'Alexander', 'Albon',      'ALB', 23, TRUE),   -- zawieszony: wszystkie punkty po terminie (demo CRON)
    ('Williams Racing',        'Logan',     'Sargeant',   'SAR',  2, FALSE),
    ('Visa Cash App RB',       'Yuki',      'Tsunoda',    'TSU', 22, FALSE),
    ('Visa Cash App RB',       'Daniel',    'Ricciardo',  'RIC',  3, FALSE),
    ('Kick Sauber',            'Valtteri',  'Bottas',     'BOT', 77, FALSE),
    ('Kick Sauber',            'Zhou',      'Guanyu',     'ZHO', 24, FALSE),
    ('MoneyGram Haas F1 Team', 'Kevin',     'Magnussen',  'MAG', 20, FALSE),
    ('MoneyGram Haas F1 Team', 'Nico',      'Hülkenberg', 'HUL', 27, FALSE)
) AS v(team, first_name, last_name, code, race_number, is_suspended)
JOIN teams t ON t.name = v.team;

INSERT INTO grands_prix (name, circuit_name, country, season_year, round_number, race_date, total_laps, is_active) VALUES
    ('2024 Bahrain Grand Prix',       'Bahrain International Circuit', 'Bahrain',      2024, 1, '2024-03-02', 57, FALSE),
    ('2024 Saudi Arabian Grand Prix', 'Jeddah Corniche Circuit',       'Saudi Arabia', 2024, 2, '2024-03-09', 50, FALSE),
    ('2024 Monaco Grand Prix',        'Circuit de Monaco',             'Monaco',       2024, 8, '2024-05-26', 78, TRUE);

INSERT INTO infringement_articles (code, title, reference, default_penalty_type, default_sanction_value, default_points, precedent_wording) VALUES
    ('APP-L-IV-2', 'Causing a Collision / Forcing Off Track', 'FIA Sporting Code - Appendix L, Chapter IV, Art. 2',
        'TIME_PENALTY', 5, 2, 'Kierowca spowodowal kolizje, nie pozostawiajac rywalowi wystarczajacej ilosci miejsca. Kara czasowa 5 sekund i 2 punkty karne.'),
    ('ART-33-3',   'Track Limits',                            'FIA Sporting Regulations, Art. 33.3',
        'TIME_PENALTY', 5, 1, 'Kierowca wielokrotnie przekroczyl limity toru, uzyskujac przewage. Kara czasowa 5 sekund i 1 punkt karny.'),
    ('ART-34-3',   'Pit Lane Infringement',                   'FIA Sporting Regulations, Art. 34.3',
        'TIME_PENALTY', 5, 1, 'Kierowca przekroczyl limit predkosci w alei serwisowej. Kara czasowa 5 sekund i 1 punkt karny.'),
    ('ART-34-14',  'Unsafe Release',                          'FIA Sporting Regulations, Art. 34.14',
        'TIME_PENALTY', 10, 2, 'Samochod zostal wypuszczony z boksu w sposob stwarzajacy zagrozenie. Kara czasowa 10 sekund i 2 punkty karne.'),
    ('ART-27-4',   'Impeding',                                'FIA Sporting Regulations, Art. 27.4',
        'REPRIMAND', NULL, 0, 'Kierowca niepotrzebnie utrudnil jazde innemu zawodnikowi. Upomnienie bez punktow karnych.'),
    ('ART-33-4',   'Leaving the Track and Gaining Advantage', 'FIA Sporting Regulations, Art. 33.4',
        'TIME_PENALTY', 5, 1, 'Kierowca opuscil tor i uzyskal trwala przewage. Kara czasowa 5 sekund i 1 punkt karny.'),
    ('ART-33-2',   'Dangerous Driving',                       'FIA Sporting Regulations, Art. 33.2',
        'DRIVE_THROUGH', NULL, 2, 'Niebezpieczny styl jazdy zagrozil innym uczestnikom wyscigu. Przejazd przez aleje serwisowa i 2 punkty karne.');

-- Konta: wspolny hash Argon2id dla hasla demo (wygenerowany w kontenerze php).
INSERT INTO users (email, password_hash, role, team_id, first_name, last_name)
SELECT v.email,
       '$argon2id$v=19$m=65536,t=4,p=1$SVZaTnJVUjVkdEZtQkFTcA$0TD/iJSCFQ5nQZNl1AkChwU+rQR/Q15TE79S9yiBSIk',
       'TEAM_PRINCIPAL', t.id, v.first_name, v.last_name
FROM (VALUES
    ('toto@mercedes.f1',        'Mercedes-AMG Petronas',  'Toto',  'W.'),
    ('principal@redbull.f1',    'Oracle Red Bull Racing', 'Szef',  'Red Bull'),
    ('principal@ferrari.f1',    'Scuderia Ferrari',       'Szef',  'Ferrari'),
    ('principal@mclaren.f1',    'McLaren F1 Team',        'Szef',  'McLaren'),
    ('principal@astonmartin.f1','Aston Martin Aramco',    'Szef',  'Aston Martin'),
    ('principal@alpine.f1',     'BWT Alpine F1 Team',     'Szef',  'Alpine'),
    ('principal@williams.f1',   'Williams Racing',        'Szef',  'Williams'),
    ('principal@rb.f1',         'Visa Cash App RB',       'Szef',  'RB'),
    ('principal@sauber.f1',     'Kick Sauber',            'Szef',  'Sauber'),
    ('principal@haas.f1',       'MoneyGram Haas F1 Team', 'Szef',  'Haas')
) AS v(email, team, first_name, last_name)
JOIN teams t ON t.name = v.team;

INSERT INTO users (email, password_hash, role, team_id, first_name, last_name) VALUES
    ('steward@fia.org',
     '$argon2id$v=19$m=65536,t=4,p=1$SVZaTnJVUjVkdEZtQkFTcA$0TD/iJSCFQ5nQZNl1AkChwU+rQR/Q15TE79S9yiBSIk',
     'FIA_STEWARD', NULL, 'John', 'D.');

-- Sprawy. Monaco: id 31-42 (3 REPORTED, 2 UNDER_INVESTIGATION, 3 PENALTY_ISSUED, 4 NO_FURTHER_ACTION),
-- zgodnie z ekranem Incidents Log; id 1-15: wczesniejsze rundy (Bahrain/Arabia Saudyjska), zrodlo punktow Superlicencji.
-- Kolejnosc kierowcow w prototypie: oskarzony, poszkodowany.
INSERT INTO incidents (id, grand_prix_id, reporting_user_id, accused_driver_id, affected_driver_id,
                       infringement_article_id, lap_number, description, status, created_at, updated_at)
OVERRIDING SYSTEM VALUE
SELECT v.id, gp.id, u.id, acc.id, aff.id, art.id, v.lap, v.description, v.status,
       now() - make_interval(hours => v.hours_ago), now() - make_interval(hours => v.hours_ago)
FROM (VALUES
    (42, '2024 Monaco Grand Prix', 'toto@mercedes.f1',         'VER', 'HAM', 'APP-L-IV-2', 24,
        'Zakret 1: spowodowanie kolizji i wypchniecie z toru. Analiza telemetrii i kamer onboard w strefie hamowania zakretu 1.', 'UNDER_INVESTIGATION', 6),
    (41, '2024 Monaco Grand Prix', 'principal@astonmartin.f1', 'MAG', NULL,  'ART-33-3',   21,
        'Przekroczenie limitow toru po raz czwarty (zakret 10). Automatyczne wykrycie potwierdzone przez petle pomiaru czasu.', 'PENALTY_ISSUED', 30),
    (40, '2024 Monaco Grand Prix', 'principal@mclaren.f1',     'SAI', 'PIA', 'ART-34-3',   18,
        'Przeciecie linii wyjazdu z alei serwisowej z utrudnieniem. Zgloszone przez dyrektora wyscigu, oczekuje na powtorke z kamer sektora.', 'REPORTED', 7),
    (39, '2024 Monaco Grand Prix', 'principal@williams.f1',    'PER', 'ALB', 'APP-L-IV-2', 12,
        'Lekki kontakt na nawrocie w pierwszym okrazeniu. Uznane za typowy incydent wyscigowy na starcie.', 'NO_FURTHER_ACTION', 54),
    (38, '2024 Monaco Grand Prix', 'principal@redbull.f1',     'STR', NULL,  'ART-34-14',   8,
        'Niebezpieczne wypuszczenie z boksu. Naruszenie art. 34.14: klucz pneumatyczny w torze jazdy.', 'PENALTY_ISSUED', 78),
    (37, '2024 Monaco Grand Prix', 'principal@sauber.f1',      'HUL', 'ZHO', 'APP-L-IV-2',  3,
        'Miejsce na wierzcholku zakretu 3 przy wyprzedzaniu. Pelna szerokosc samochodu zachowana przez caly zakret.', 'NO_FURTHER_ACTION', 80),
    (36, '2024 Monaco Grand Prix', 'toto@mercedes.f1',         'ALO', 'RUS', 'ART-27-4',   31,
        'Obrona pozycji: spozniony ruch przy hamowaniu. Zgloszone przez zespol, oczekuje na material z kamer.', 'REPORTED', 5),
    (35, '2024 Monaco Grand Prix', 'principal@ferrari.f1',     'NOR', 'LEC', 'APP-L-IV-2', 28,
        'Kontakt przy wyprzedzaniu w zakrecie 8. Analiza materialu z kamery pokladowej i telemetrii.', 'UNDER_INVESTIGATION', 9),
    (34, '2024 Monaco Grand Prix', 'principal@ferrari.f1',     'TSU', NULL,  'ART-34-3',   26,
        'Predkosc w alei serwisowej powyzej limitu. Pomiar 83 km/h przy limicie 80 km/h.', 'PENALTY_ISSUED', 28),
    (33, '2024 Monaco Grand Prix', 'principal@alpine.f1',      'BOT', 'OCO', 'ART-33-2',   22,
        'Zmiana toru jazdy na prostej start-meta. Zgloszone przez zespol, oczekuje na decyzje.', 'REPORTED', 3),
    (32, '2024 Monaco Grand Prix', 'principal@mclaren.f1',     'GAS', NULL,  'ART-33-4',   17,
        'Zjazd poza tor w zakrecie 5. Brak przewagi, powrot na tor bez zagrozenia.', 'NO_FURTHER_ACTION', 100),
    (31, '2024 Monaco Grand Prix', 'principal@williams.f1',    'RIC', 'SAR', 'APP-L-IV-2',  9,
        'Kontakt kol w zakrecie 6. Typowy incydent wyscigowy, brak winnego.', 'NO_FURTHER_ACTION', 120),
    ( 1, '2024 Bahrain Grand Prix',       'principal@ferrari.f1',  'MAG', NULL, 'ART-33-3', 12, 'Wielokrotne przekroczenie limitow toru, uzyskanie przewagi czasowej.', 'PENALTY_ISSUED', 8328),
    ( 2, '2024 Saudi Arabian Grand Prix', 'principal@ferrari.f1',  'MAG', NULL, 'ART-33-3', 30, 'Wielokrotne przekroczenie limitow toru, uzyskanie przewagi czasowej.', 'PENALTY_ISSUED', 4800),
    ( 3, '2024 Bahrain Grand Prix',       'principal@ferrari.f1',  'MAG', NULL, 'ART-33-3', 41, 'Wielokrotne przekroczenie limitow toru, uzyskanie przewagi czasowej.', 'PENALTY_ISSUED', 2880),
    ( 4, '2024 Saudi Arabian Grand Prix', 'principal@mclaren.f1',  'PER', NULL, 'ART-34-14', 6, 'Niebezpieczne wypuszczenie z boksu w trakcie neutralizacji.', 'PENALTY_ISSUED', 7392),
    ( 5, '2024 Bahrain Grand Prix',       'principal@mclaren.f1',  'PER', NULL, 'ART-33-3', 19, 'Przekroczenie limitow toru w zakretach 9 i 10.', 'PENALTY_ISSUED', 3600),
    ( 6, '2024 Saudi Arabian Grand Prix', 'principal@mclaren.f1',  'PER', NULL, 'ART-33-4', 27, 'Opuszczenie toru z utrzymaniem pozycji.', 'PENALTY_ISSUED', 2160),
    ( 7, '2024 Bahrain Grand Prix',       'toto@mercedes.f1',      'STR', NULL, 'ART-34-14', 5, 'Niebezpieczne wypuszczenie z boksu, kontakt z personelem zespolu.', 'PENALTY_ISSUED', 5976),
    ( 8, '2024 Saudi Arabian Grand Prix', 'principal@ferrari.f1',  'VER', NULL, 'ART-33-3', 22, 'Przekroczenie limitow toru na wyjsciu z zakretu 1.', 'PENALTY_ISSUED', 4920),
    ( 9, '2024 Bahrain Grand Prix',       'principal@ferrari.f1',  'VER', NULL, 'ART-33-3', 35, 'Przekroczenie limitow toru na wyjsciu z zakretu 4.', 'PENALTY_ISSUED', 1440),
    (10, '2024 Saudi Arabian Grand Prix', 'principal@redbull.f1',  'TSU', NULL, 'ART-34-3', 14, 'Przekroczenie limitu predkosci w alei serwisowej.', 'PENALTY_ISSUED', 6984),
    (11, '2024 Bahrain Grand Prix',       'principal@redbull.f1',  'RUS', NULL, 'ART-33-3', 33, 'Przekroczenie limitow toru, uzyskanie przewagi czasowej.', 'PENALTY_ISSUED', 3960),
    (12, '2024 Saudi Arabian Grand Prix', 'principal@mclaren.f1',  'ALB', NULL, 'ART-33-2', 10, 'Niebezpieczna jazda w strefie hamowania, zagrozenie dla rywala.', 'PENALTY_ISSUED', 8880),
    (13, '2024 Bahrain Grand Prix',       'principal@mclaren.f1',  'ALB', NULL, 'ART-33-2', 20, 'Niebezpieczna jazda w strefie hamowania, zagrozenie dla rywala.', 'PENALTY_ISSUED', 8856),
    (14, '2024 Saudi Arabian Grand Prix', 'principal@mclaren.f1',  'ALB', NULL, 'ART-33-2', 30, 'Niebezpieczna jazda w strefie hamowania, zagrozenie dla rywala.', 'PENALTY_ISSUED', 8832),
    (15, '2024 Bahrain Grand Prix',       'principal@mclaren.f1',  'ALB', NULL, 'ART-33-2', 40, 'Niebezpieczna jazda w strefie hamowania, zagrozenie dla rywala.', 'PENALTY_ISSUED', 8808)
) AS v(id, gp, reporter, accused, affected, article, lap, description, status, hours_ago)
JOIN grands_prix gp             ON gp.name = v.gp
JOIN users u                    ON u.email = v.reporter
JOIN drivers acc                ON acc.code = v.accused
LEFT JOIN drivers aff           ON aff.code = v.affected
JOIN infringement_articles art  ON art.code = v.article;

SELECT setval(pg_get_serial_sequence('incidents', 'id'), (SELECT max(id) FROM incidents));

-- Kary. Punkty aktywne: MAG 10, PER 7, STR 5, VER 4, TSU 3, RUS 2; ALB ma 12 pkt po terminie (is_expired = false),
-- wiec `php bin/cron.php expire-penalties` je przedawni i odwiesi kierowce.
INSERT INTO penalties (incident_id, driver_id, steward_id, article_id, penalty_type, sanction_value,
                       points_added, reason, issued_at, expires_at, is_expired)
SELECT v.incident_id, i.accused_driver_id, s.id, i.infringement_article_id, 'TIME_PENALTY', v.seconds,
       v.points, v.reason, now() - make_interval(days => v.days_ago),
       now() - make_interval(days => v.days_ago) + interval '365 days', FALSE
FROM (VALUES
    (41,  5, 2,   2, 'Czwarte przekroczenie limitow toru w zakrecie 10, kara czasowa 5 s.'),
    (38, 10, 2,   3, 'Niebezpieczne wypuszczenie z boksu, kara czasowa 10 s.'),
    (34,  5, 1,   1, 'Przekroczenie limitu predkosci w alei serwisowej (83 km/h), kara czasowa 5 s.'),
    ( 1,  5, 3, 347, 'Powtarzane przekroczenia limitow toru.'),
    ( 2,  5, 3, 200, 'Powtarzane przekroczenia limitow toru.'),
    ( 3,  5, 2, 120, 'Przekroczenie limitow toru.'),
    ( 4, 10, 3, 308, 'Niebezpieczne wypuszczenie z boksu.'),
    ( 5,  5, 2, 150, 'Przekroczenie limitow toru.'),
    ( 6,  5, 2,  90, 'Opuszczenie toru z utrzymaniem pozycji.'),
    ( 7, 10, 3, 249, 'Niebezpieczne wypuszczenie z boksu z kontaktem z personelem.'),
    ( 8,  5, 2, 205, 'Przekroczenie limitow toru.'),
    ( 9,  5, 2,  60, 'Przekroczenie limitow toru.'),
    (10,  5, 2, 291, 'Przekroczenie limitu predkosci w alei serwisowej.'),
    (11,  5, 2, 165, 'Przekroczenie limitow toru.'),
    (12, 10, 3, 370, 'Niebezpieczna jazda w strefie hamowania.'),
    (13, 10, 3, 369, 'Niebezpieczna jazda w strefie hamowania.'),
    (14, 10, 3, 368, 'Niebezpieczna jazda w strefie hamowania.'),
    (15, 10, 3, 367, 'Niebezpieczna jazda w strefie hamowania.')
) AS v(incident_id, seconds, points, days_ago, reason)
JOIN incidents i ON i.id = v.incident_id
CROSS JOIN (SELECT id FROM users WHERE email = 'steward@fia.org') s;
