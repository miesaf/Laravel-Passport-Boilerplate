# Laravel Passport Boilerplate

A backend API boilerplate on **Laravel 13 / PHP 8.5**:

- Login with OAuth2 access + refresh tokens (Laravel Passport password grant), or a long-lived
  personal access token (`"pat": true`), refresh and logout (token revocation)
- First-time login (set password), change password, forgot/reset password by email
- 16 configurable password policies (length, complexity, minimum/maximum age, reuse, lockout on
  failed attempts, dormant accounts, compromised passwords, ...)
- Users, roles and permissions (spatie/laravel-permission, wildcard permissions such as `users.*`)
- Reference codes ("options") and an audit log of sensitive actions

Its companion front end is [Vue Passport Boilerplate](https://github.com/miesaf/Vue-Passport-Boilerplate).

## Setup

```bash
composer install
cp .env.example .env            # set DB_*, APP_URL, APP_FE
php artisan key:generate
php artisan passport:keys       # writes storage/app/secrets/oauth/oauth-*.key (never commit them)
php artisan migrate --seed      # demo users admin / moderator / user, password "password"
php artisan passport:client --personal --name="Personal Access Client" --provider=users -n
php artisan passport:client --password --name="Password Grant Client" --provider=users -n
```

Put the password grant client's ID and secret in `PASSPORT_CLIENT_ID` / `PASSPORT_CLIENT_SECRET`
(the secret is shown once; Passport stores only its hash).

Login posts to this app's own `/oauth/token` (`PASSPORT_LOGIN_ENDPOINT`, default `APP_URL/oauth/token`),
so the local server needs more than one worker:

```bash
PHP_CLI_SERVER_WORKERS=4 php artisan serve --no-reload
```

## API

All routes are under `/api`. Send `Accept: application/json`; authenticated routes take
`Authorization: Bearer <access_token>`.

| Route | Purpose |
|---|---|
| `POST login` (`user_id`, `password`, optional `pat`) | tokens + user with roles and permissions |
| `POST refreshToken` (`refresh_token`) | new token pair |
| `POST logout` | revoke the bearer token and its refresh tokens |
| `POST password/firstTime` (`user_id`, `new_password`) | set the first password |
| `POST password/email` (`user_id`) | email a reset link to `APP_FE/password/reset/<token>` (valid 60 minutes) |
| `POST password/reset` (`token`, `new_password`) | reset the password |
| `GET user`, `POST user/changePassword` | own profile, change password |
| `permissions`, `roles`, `users`, `options`, `pwdPolicies`, `auditLogs` | management screens (permission checked per action) |

## Tests

```bash
php artisan test
```
