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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') ||
            str_contains(request()->getHost(), 'ngrok-free.app')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');

            // This is the key part for asset loading
            config(['app.asset_url' => url('/')]);
        }
    }
}
