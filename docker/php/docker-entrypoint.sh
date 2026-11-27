#!/bin/sh
set -e

cd /app

# The bind mount `.:/app` hides anything installed into the image,
# so make sure the Composer autoloader exists (or is up to date) before PHP-FPM starts.
if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    echo "[entrypoint] Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Writable dirs for PHP-FPM (www-data): evidence uploads and logs. In production these are named
# volumes shared by php, cron and server; creating the log files here keeps them owned by www-data
# even when the cron container is the first writer. Bind mounts may refuse chown, which is harmless.
mkdir -p storage/logs public/uploads
touch storage/logs/app.log storage/logs/cron.log
chown www-data:www-data storage/logs public/uploads storage/logs/app.log storage/logs/cron.log 2>/dev/null \
    || echo "[entrypoint] chown skipped (bind mount)"

exec "$@"
