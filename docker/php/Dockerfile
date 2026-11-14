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
      echo 'memory_limit = -1'; \
      echo 'upload_max_filesize = 512M'; \
      echo 'post_max_size = 512M'; \
      echo 'max_file_uploads = 20'; \
    } > /usr/local/etc/php/conf.d/app.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
# Strip CRLF (Windows checkouts) so the shebang line works.
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . .

# Autoloader baked into the image (used when /app is not bind-mounted, e.g. deploy).
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
