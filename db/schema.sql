-- ApexSteward (FIA Stewards Hub) - schemat PostgreSQL 16 (8 tabel, 3NF)
-- Widok v_super_licence_standings i trigger set_updated_at: karta S1-08.
-- Ladowane przez Docker (docker-entrypoint-initdb.d) tylko na pustym wolumenie.

-- Akcje na kluczach obcych (K26); ON UPDATE zostaje domyslne NO ACTION, bo klucze IDENTITY sa niezmienne.
--   RESTRICT: users/drivers -> teams (nie usuwa sie zespolu z kierowcami i kontami),
--             incidents -> grands_prix, users, drivers, infringement_articles (zgloszenie to dokument),
--             penalties -> incidents, drivers, users, infringement_articles (orzeczenie FIA chroni historie kar).
--   CASCADE:  incident_evidence -> incidents (dowod nie istnieje bez sprawy; plik z dysku usuwa serwis).

CREATE TABLE teams (
    id              INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    license_country VARCHAR(3)   NOT NULL,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE TABLE users (
    id            INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email         VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(20)  NOT NULL CHECK (role IN ('TEAM_PRINCIPAL', 'FIA_STEWARD')),
    team_id       INT REFERENCES teams(id) ON DELETE RESTRICT,
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT now(),
    CONSTRAINT ck_users_role_team CHECK (
        (role = 'TEAM_PRINCIPAL' AND team_id IS NOT NULL) OR
        (role = 'FIA_STEWARD'    AND team_id IS NULL))
);

CREATE TABLE drivers (
    id           INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    team_id      INT NOT NULL REFERENCES teams(id) ON DELETE RESTRICT,
    first_name   VARCHAR(100) NOT NULL,
    last_name    VARCHAR(100) NOT NULL,
    code         CHAR(3)      NOT NULL UNIQUE,          -- VER, HAM (widoczne w tabelach)
    race_number  INT          NOT NULL UNIQUE CHECK (race_number BETWEEN 1 AND 99),
    is_suspended BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE TABLE grands_prix (
    id           INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,                 -- 2024 Monaco Grand Prix
    circuit_name VARCHAR(150) NOT NULL,
    country      VARCHAR(80)  NOT NULL,
    season_year  INT  NOT NULL CHECK (season_year >= 2024),
    round_number INT  NOT NULL CHECK (round_number BETWEEN 1 AND 30),
    race_date    DATE NOT NULL,
    total_laps   INT  NOT NULL CHECK (total_laps > 0),  -- walidacja numeru okrążenia
    is_active    BOOLEAN NOT NULL DEFAULT FALSE,        -- „Active session” w nagłówkach
    CONSTRAINT uq_season_round UNIQUE (season_year, round_number)
);
CREATE UNIQUE INDEX uq_single_active_gp ON grands_prix (is_active) WHERE is_active;

CREATE TABLE infringement_articles (                    -- katalog regulaminu i presety kar
    id                     INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    code                   VARCHAR(40)  NOT NULL UNIQUE, -- APP-L-IV-2
    title                  VARCHAR(150) NOT NULL,        -- Causing a Collision / Forcing Off Track
    reference              VARCHAR(200) NOT NULL,        -- FIA Sporting Code - Appendix L, Chapter IV, Art. 2
    default_penalty_type   VARCHAR(20)  NOT NULL CHECK (default_penalty_type IN
        ('TIME_PENALTY', 'GRID_DROP', 'DRIVE_THROUGH', 'REPRIMAND', 'DISQUALIFICATION')),
    default_sanction_value INT,
    default_points         INT NOT NULL DEFAULT 0 CHECK (default_points BETWEEN 0 AND 3),
    precedent_wording      TEXT NOT NULL                 -- „Insert Precedent Wording”
);

CREATE TABLE incidents (
    id                     INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    grand_prix_id          INT NOT NULL REFERENCES grands_prix(id)           ON DELETE RESTRICT,
    reporting_user_id      INT NOT NULL REFERENCES users(id)                 ON DELETE RESTRICT,
    accused_driver_id      INT NOT NULL REFERENCES drivers(id)               ON DELETE RESTRICT,
    affected_driver_id     INT REFERENCES drivers(id)                        ON DELETE RESTRICT, -- kierowca zgłaszającego zespołu (opcjonalny; kolumna „vs” w prototypie)
    infringement_article_id INT NOT NULL REFERENCES infringement_articles(id) ON DELETE RESTRICT,
    lap_number             INT NOT NULL CHECK (lap_number > 0),
    description            TEXT NOT NULL CHECK (char_length(description) BETWEEN 20 AND 500),
    status                 VARCHAR(20) NOT NULL DEFAULT 'REPORTED'
        CHECK (status IN ('REPORTED', 'UNDER_INVESTIGATION', 'PENALTY_ISSUED', 'NO_FURTHER_ACTION')),
    created_at             TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at             TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_incident_distinct_drivers CHECK (affected_driver_id IS DISTINCT FROM accused_driver_id)
);

CREATE TABLE incident_evidence (
    id            INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    incident_id   INT NOT NULL REFERENCES incidents(id) ON DELETE CASCADE,
    file_path     VARCHAR(255) NOT NULL UNIQUE,          -- względna, np. /uploads/a1b2….png
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(20)  NOT NULL CHECK (mime_type IN ('image/jpeg', 'image/png')),
    size_bytes    INT NOT NULL CHECK (size_bytes BETWEEN 1 AND 5242880),
    sha256        CHAR(64) NOT NULL,
    uploaded_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE penalties (
    id             INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    incident_id    INT NOT NULL REFERENCES incidents(id)              ON DELETE RESTRICT,
    driver_id      INT NOT NULL REFERENCES drivers(id)                ON DELETE RESTRICT,
    steward_id     INT NOT NULL REFERENCES users(id)                  ON DELETE RESTRICT,
    article_id     INT NOT NULL REFERENCES infringement_articles(id)  ON DELETE RESTRICT,
    penalty_type   VARCHAR(20) NOT NULL CHECK (penalty_type IN
        ('TIME_PENALTY', 'GRID_DROP', 'DRIVE_THROUGH', 'REPRIMAND', 'DISQUALIFICATION')),
    sanction_value INT,                                    -- sekundy lub miejsca
    points_added   INT NOT NULL DEFAULT 0 CHECK (points_added BETWEEN 0 AND 3),
    reason         TEXT NOT NULL,
    issued_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    expires_at     TIMESTAMPTZ NOT NULL,                   -- liczone w serwisie: issued_at + 365 dni
    is_expired     BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT uq_penalty_incident_driver UNIQUE (incident_id, driver_id),
    CONSTRAINT ck_penalty_expiry CHECK (expires_at > issued_at),
    CONSTRAINT ck_penalty_value CHECK (
        (penalty_type IN ('TIME_PENALTY', 'GRID_DROP') AND sanction_value IS NOT NULL AND sanction_value > 0) OR
        (penalty_type NOT IN ('TIME_PENALTY', 'GRID_DROP') AND sanction_value IS NULL))
);


-- Indeksy (poza PK/UNIQUE)
CREATE INDEX idx_drivers_team            ON drivers   (team_id);
CREATE INDEX idx_incidents_grand_prix    ON incidents (grand_prix_id);
CREATE INDEX idx_incidents_status        ON incidents (status);
CREATE INDEX idx_incidents_accused       ON incidents (accused_driver_id);
CREATE INDEX idx_incidents_reporter      ON incidents (reporting_user_id);
CREATE INDEX idx_incident_evidence_inc   ON incident_evidence (incident_id);
CREATE INDEX idx_penalties_active_driver ON penalties (driver_id)  WHERE NOT is_expired;
CREATE INDEX idx_penalties_active_expiry ON penalties (expires_at) WHERE NOT is_expired;
