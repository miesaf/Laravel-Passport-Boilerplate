#!/usr/bin/env sh
set -e

# Move to application directory
cd /var/www/html

# If APP_KEY is missing but APP_KEY_GENERATE=true, generate it (not recommended for production)
if [ -z "$APP_KEY" ] && [ "$APP_KEY_GENERATE" = "true" ]; then
  php artisan key:generate --ansi
fi

# Ensure storage and cache dirs exist and are writable
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Create storage symlink if missing
if [ ! -e public/storage ] && [ "$STORAGE_LINK" = "true" ]; then
  php artisan storage:link || true
fi

# Optional: run migrations automatically (set RUN_MIGRATIONS=true for automatic migration)
if [ "$RUN_MIGRATIONS" = "true" ]; then
  # Wait for DB if DB_HOST is set (simple loop)
  if [ -n "$DB_HOST" ]; then
    echo "Waiting for DB at $DB_HOST..."
    RET=1
    n=0
    until [ $RET -eq 0 ] || [ $n -ge 30 ]; do
      php -r "try{ \$pdo=new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT')?:3306) . ';dbname=' . (getenv('DB_DATABASE')?:''), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'db ok'; } catch(Exception \$e) { exit(1);} " && RET=0 || RET=1
      n=$((n+1))
      [ $RET -ne 0 ] && sleep 2
    done
  fi

  php artisan migrate --force
fi

# Optional: run passport install (use with care -- generates client secrets)
if [ "$RUN_PASSPORT_INSTALL" = "true" ]; then
  php artisan passport:install --force || true
fi

# Exec CMD (php-fpm)
exec "$@"
