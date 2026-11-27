#!/usr/bin/env bash
# Backup of the production stack (docker-compose.prod.yaml) into ./backups, no sudo or host cron:
#   database  -> backups/apexsteward-db-<UTC>.sql.gz   (pg_dump, plain SQL with DROP ... IF EXISTS)
#   evidence  -> backups/apexsteward-uploads-<UTC>.tar.gz
# Usage on the VPS, from the repository root:  bash docker/db/backup.sh
# Restore:  gunzip -c backups/<file>.sql.gz | docker compose -f docker-compose.prod.yaml exec -T db \
#             sh -c 'psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
# Optional env: ENV_FILE (default .env), BACKUP_DIR (default backups), COMPOSE_PROJECT_NAME.
set -euo pipefail

cd "$(dirname "$0")/../.."
compose=(docker compose -f docker-compose.prod.yaml --env-file "${ENV_FILE:-.env}")
dir="${BACKUP_DIR:-backups}"
stamp="$(date -u +%Y%m%dT%H%M%SZ)"

umask 077
mkdir -p "$dir"

db_file="$dir/apexsteward-db-$stamp.sql.gz"
"${compose[@]}" exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" --clean --if-exists --no-owner' \
    | gzip > "$db_file"

uploads_file="$dir/apexsteward-uploads-$stamp.tar.gz"
"${compose[@]}" exec -T php tar czf - -C /app/public uploads > "$uploads_file"

ls -l "$db_file" "$uploads_file"
