<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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
        Carbon::setLocale('fr');

        Paginator::defaultView('vendor.pagination.pass');
        Paginator::defaultSimpleView('vendor.pagination.pass');

        RateLimiter::for('login', function (Request $request) {
            $identity = strtolower(trim((string) $request->input('email')));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by('login-account:'.hash('sha256', $identity.'|'.$ip)),
                Limit::perMinute(5)->by('login-ip:'.$ip),
            ];
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');

            // Cookies de session uniquement en HTTPS (défaut durci en production)
            config()->set('session.secure', env('SESSION_SECURE_COOKIE', true));
        }
    }
}
