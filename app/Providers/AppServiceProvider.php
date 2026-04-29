<?php

namespace App\Providers;

use App\Services\Vcs\VcsProviderManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        View::composer(['auditor', 'imports'], function ($view): void {
            $request = request();
            /** @var VcsProviderManager $manager */
            $manager = app(VcsProviderManager::class);
            $providers = $manager->providerSummaries($request);
            $defaultKey = $manager->defaultProviderKey($request);
            $defaultProvider = collect($providers)->firstWhere('key', $defaultKey) ?? $providers[0] ?? null;

            $view->with([
                'vcsProviders' => $providers,
                'defaultVcsProviderKey' => $defaultKey,
                'defaultVcsProvider' => $defaultProvider,
            ]);
        });
    }
}
