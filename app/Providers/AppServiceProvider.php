<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        $appUrl = (string) config('app.url', '');
        $forceHttps = (bool) env('APP_FORCE_HTTPS', false)
            || app()->environment('production')
            || str_starts_with($appUrl, 'https://');

        if ($forceHttps) {
            URL::forceScheme('https');

            if ($appUrl !== '') {
                URL::forceRootUrl($appUrl);
            }
        }
    }
}
