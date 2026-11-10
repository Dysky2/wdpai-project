-- ApexSteward (FIA Stewards Hub) - Database Initialization
-- Pełny schemat tabel i seed danych demonstracyjnych zostanie rozbudowany w Sprint 1 (K10, K11)

CREATE TABLE IF NOT EXISTS _init_check (
    id SERIAL PRIMARY KEY,
    initialized_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    message VARCHAR(255) DEFAULT 'ApexSteward DB initialized successfully'
);
