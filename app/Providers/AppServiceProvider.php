<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
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

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
