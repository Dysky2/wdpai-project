FROM postgres:16-alpine

# Schema and seed baked in for deployments without bind mounts (docker-compose.prod.yaml).
# Run only on an empty data volume; in dev the ./db bind mount overlays this directory.
COPY db/ /docker-entrypoint-initdb.d/
