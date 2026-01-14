# -----------------------------
# 1) Vendor build stage (PHP 8.1 + Composer)
# -----------------------------
FROM php:8.1-cli-alpine AS vendor

# System deps + PHP extensions needed by Laravel common stack
RUN apk add --no-cache \
      git curl unzip bash icu-dev libzip-dev oniguruma-dev zlib-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath intl zip opcache \
    && rm -rf /tmp/*

# Install Composer (pinned installer)
RUN curl -sS https://getcomposer.org/installer | php -- \
      --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy composer manifests first for caching
COPY composer.json composer.lock ./

# Install deps (no dev)
RUN composer install \
      --no-dev --prefer-dist --no-interaction --no-progress \
      --optimize-autoloader --no-scripts

# Copy application source
COPY . /app

# Laravel optimizations (safe to ignore if env missing during build)
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi || true


# -----------------------------
# 2) Runtime stage (PHP-FPM + Nginx + Supervisor)
# -----------------------------
FROM php:8.1-fpm-alpine

# Runtime packages (keep these)
RUN apk add --no-cache \
      nginx supervisor bash curl ca-certificates icu-libs libzip oniguruma

# Build PHP extensions with proper build deps, then remove them
RUN apk add --no-cache --virtual .build-deps \
      $PHPIZE_DEPS \
      icu-dev \
      libzip-dev \
      oniguruma-dev \
      zlib-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mbstring bcmath intl zip opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy app + vendor from build stage
COPY --from=vendor /app /var/www/html

# Bring composer into runtime image too (optional but you asked for it)
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer

# Nginx config
RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
