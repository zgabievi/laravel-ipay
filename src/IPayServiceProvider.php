<?php

namespace Zorb\IPay;

use Illuminate\Support\ServiceProvider;

class IPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/config/ipay.php' => config_path('ipay.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/config/ipay.php', 'ipay');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(IPay::class);
    }
}
