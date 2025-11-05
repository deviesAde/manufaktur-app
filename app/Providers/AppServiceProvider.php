<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
 public function boot()
{
    // Force HTTPS selalu, regardless of environment
    URL::forceScheme('https');

    if($this->app->environment('production')) {
        $this->app['request']->setTrustedProxies(
            ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
        );
    }
}
}
