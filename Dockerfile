# Build stage: install composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Use --no-dev in CI or production builds
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Production PHP-FPM image
FROM php:8.1-fpm-alpine
# Install common PHP extensions required for Laravel
RUN apk add --no-cache --virtual .build-deps \
        zlib-dev libzip-dev oniguruma-dev autoconf g++ make gcc musl-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath opcache \
    && apk del .build-deps \
    && apk add --no-cache curl bash ca-certificates

# Create www directory
WORKDIR /var/www/html

# Copy composer vendor from build stage
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy app sources
# Note: copy everything except .env (you should supply environment via k8s Secrets/ConfigMaps)
COPY . /var/www/html

# Make sure permissions are correct
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Copy entrypoint that runs any runtime tasks (see entrypoint.sh below)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose socket/port for php-fpm
EXPOSE 9000

USER www-data
ENV PATH="/var/www/html/vendor/bin:${PATH}"

# Use entrypoint to run optional runtime tasks (storage link, migrations if enabled)
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
