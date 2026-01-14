# ------------------------------------------------------------
# 1) Build stage: composer install + optimized autoload
# ------------------------------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy only composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (no dev)
RUN composer install \
  --no-dev --prefer-dist --no-interaction --no-progress \
  --optimize-autoloader --no-scripts

# Copy the full app
COPY . .

# Now that app exists, run scripts that may need it (best-effort)
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi || true

# ------------------------------------------------------------
# 2) Runtime stage: PHP-FPM + Nginx on port 80
# ------------------------------------------------------------
FROM php:8.1-fpm-alpine

# Install runtime deps + nginx + supervisor (to run nginx+php-fpm)
RUN apk add --no-cache \
    nginx supervisor bash curl ca-certificates \
    icu-libs libzip oniguruma \
 && apk add --no-cache --virtual .build-deps \
    icu-dev libzip-dev oniguruma-dev \
 && docker-php-ext-install pdo_mysql mbstring bcmath intl opcache \
 && apk del .build-deps \
 && mkdir -p /var/www/html /run/nginx /var/log/supervisor

WORKDIR /var/www/html

# Copy app (including vendor) from build stage
COPY --from=vendor /app /var/www/html

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor config (runs nginx + php-fpm)
COPY docker/supervisord.conf /etc/supervisord.conf

# Your existing entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 80

# Keep your artisan bootstrap logic, then run supervisord
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
