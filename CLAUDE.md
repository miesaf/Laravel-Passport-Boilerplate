# CLAUDE.md

**Laravel Passport Boilerplate** — an API-only Laravel backend (Passport OAuth2 login, password policies,
spatie roles/permissions, reference codes, audit log) for the Vue Passport Boilerplate front end.
Keep the API routes and JSON response shapes stable: the front end depends on them.

## History

- 2021–2023: Laravel 8, a half-done Laravel 9 upgrade (`laravel-9` branch, commit 265e804) was what
  production ran; its database was never migrated, so the site answered 500.
- 2026-11-26: upgraded to **Laravel 13 / PHP 8.5** on the slim skeleton (ClickUp `86d4e29q4`, under the
  app-archive PHP 8.5 move `86d4e29pu`). Passport 13, spatie/laravel-permission 8. laravel/ui, sanctum,
  fruitcake/laravel-cors, doctrine/dbal, debugbar and the Node tooling were dropped.

## How it works

- `LoginController@login` checks the password policies, then posts the password grant to this app's own
  `/oauth/token` with Laravel's HTTP client (`config('app.passport_login_endpoint')`). `artisan serve`
  therefore needs `PHP_CLI_SERVER_WORKERS` > 1 and `--no-reload`, or it deadlocks.
- Passport keys are loaded from `storage/app/secrets/oauth/` (`AppServiceProvider`), git-ignored.
  Client IDs are UUIDs and secrets are hashed (Passport 13); the password grant is enabled explicitly.
- Password policies are rows 1–16 of `password_policies`, read by id in `App\Http\Controllers\Controller`.
  Carbon 3 `diffIn*()` is signed: always call it as `$past->diffInDays($now)`.
- Permissions use guard `api`; wildcard permissions are on (`users.*` grants `users.list`).
- `audit_log()` stores the raw request with Authorization/Cookie headers and password/token fields masked.
- `App\Console\ComposerScripts::discoverPackages` builds the package manifest inside Composer, because the
  host's PHP 8.5 CLI has `proc_open` disabled.

## Deploy (archive host, folder served at `/<folder>/public`)

No path overrides; `APP_URL` = the `/public` URL. In the folder, with the PHP 8.5 CLI `/opt/alt/php85/usr/bin/php`:

1. `git pull`, then `composer install --no-dev --optimize-autoloader`
2. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `CACHE_STORE=file`, `MAIL_*`; first deploy only:
   `artisan passport:keys`, `artisan migrate --force --seed`, create the personal access and password
   grant clients (`artisan passport:client --personal|--password --provider=users -n`) and put the
   password client's ID/secret in `PASSPORT_CLIENT_ID` / `PASSPORT_CLIENT_SECRET`
3. `artisan migrate --force` (when there are migrations), `artisan optimize`

No scheduler on the host, so `passport:purge` (scheduled hourly) does not run there.

## Commands

```bash
composer install && php artisan test
PHP_CLI_SERVER_WORKERS=4 php artisan serve --no-reload
php artisan migrate:fresh --seed
composer audit
```
