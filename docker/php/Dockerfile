FROM php:8.3-fpm-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN apk add --no-cache \
        zlib \
        libzip \
        libpng \
        libjpeg-turbo \
        postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
        zlib-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        postgresql-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        opcache \
        zip \
        gd \
        bcmath \
        pgsql \
        pdo_pgsql \
    && apk del .build-deps

RUN { \
      echo 'memory_limit = 256M'; \
      echo 'upload_max_filesize = 5M'; \
      echo 'post_max_size = 8M'; \
      echo 'max_file_uploads = 20'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
      echo 'expose_php = Off'; \
    } > /usr/local/etc/php/conf.d/app.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
# Strip CRLF (Windows checkouts) so the shebang line works.
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . .

# local (default, docker-compose.yaml) or production (docker-compose.prod.yaml).
ARG APP_BUILD_ENV=local

# Autoloader baked into the image (used when /app is not bind-mounted, e.g. deploy).
# Production: no dev dependencies, opcache never re-checks files, cron job runs as www-data.
RUN if [ "$APP_BUILD_ENV" = "production" ]; then \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
        && echo 'opcache.validate_timestamps = 0' > /usr/local/etc/php/conf.d/zz-opcache-production.ini \
        && sed 's/\r$//' docker/cron/crontab > /etc/crontabs/www-data; \
    else \
        composer install --no-interaction --prefer-dist --optimize-autoloader; \
    fi \
    && mkdir -p storage/logs public/uploads \
    && chown www-data:www-data storage/logs public/uploads

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
