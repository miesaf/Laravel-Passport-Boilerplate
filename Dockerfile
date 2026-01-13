# Dockerfile (use php 8.1 in build stage so composer uses PHP 8.1)
FROM php:8.1-cli-alpine AS vendor

# Install build dependencies and PHP extensions required for Composer packages
RUN apk add --no-cache --virtual .build-deps \
        zlib-dev libzip-dev oniguruma-dev autoconf g++ make gcc musl-dev \
        curl bash git \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath opcache \
    && apk del .build-deps

WORKDIR /app
COPY composer.json composer.lock ./

# Install Composer (ensure latest installer) and install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Production PHP-FPM image (use matching PHP 8.1)
FROM php:8.1-fpm-alpine

# Install runtime deps and PHP extensions
RUN apk add --no-cache curl bash ca-certificates \
    && apk add --no-cache libzip oniguruma \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath opcache || true

WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy app sources (but keep .env out; supply via k8s secret)
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Entrypoint/script as needed (adjust path if you put it elsewhere)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
USER www-data
ENV PATH="/var/www/html/vendor/bin:${PATH}"
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
