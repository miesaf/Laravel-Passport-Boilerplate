<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::loadKeysFrom(__DIR__.'/../../storage/app/secrets/oauth');

        Passport::tokensExpireIn(now()->addMinutes(config('app.passport_tokens_expire_in')));
        Passport::refreshTokensExpireIn(now()->addMinutes(config('app.passport_refresh_tokens_expire_in')));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
