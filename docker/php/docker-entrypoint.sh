#!/bin/sh
set -e

cd /app

# The bind mount `.:/app` hides anything installed into the image,
# so make sure the Composer autoloader exists (or is up to date) before PHP-FPM starts.
if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    echo "[entrypoint] Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

exec "$@"
