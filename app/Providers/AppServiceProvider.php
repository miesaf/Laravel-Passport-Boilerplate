<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keys live outside the default storage/ root; create them with `php artisan passport:keys`.
        Passport::loadKeysFrom(storage_path('app/secrets/oauth'));

        // Login and refresh token use the password grant (disabled by default since Passport 12).
        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(now()->addMinutes(config('app.passport_tokens_expire_in')));
        Passport::refreshTokensExpireIn(now()->addMinutes(config('app.passport_refresh_tokens_expire_in')));
        Passport::personalAccessTokensExpireIn(now()->addDays(config('app.passport_personal_access_tokens_expire_in')));

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json(['status' => false, 'message' => 'Request blocked due to too many attempts. Cool down period initiated. Please try again later after ONE (1) minute.'], 429);
                });
        });
    }
}
