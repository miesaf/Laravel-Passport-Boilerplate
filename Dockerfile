# Build stage: use PHP 8.1 so it matches composer.lock constraints
FROM php:8.1-cli-alpine AS vendor

# Install system deps needed for common PHP extensions and composer
RUN apk add --no-cache \
        zlib-dev libzip-dev oniguruma-dev icu-dev openssl-dev \
        bash git curl unzip make g++ autoconf gcc musl-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath opcache intl \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip \
    && apk del autoconf gcc g++ make musl-dev || true

WORKDIR /app

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only composer files to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP dependencies but skip scripts that call artisan (artisan not copied yet)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

# Copy application files
COPY . /app

# Run any composer scripts that need the app files (try package discovery; ignore errors)
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi || true

# Production image
FROM php:8.1-fpm-alpine

# Runtime deps
RUN apk add --no-cache bash curl ca-certificates libzip oniguruma icu \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath opcache intl || true

WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=vendor /app/vendor /var/www/html/vendor
# Copy the rest of the app
COPY --from=vendor /app /var/www/html

# Ensure permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Entrypoint (if you have one)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
USER www-data
ENV PATH="/var/www/html/vendor/bin:${PATH}"
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
