<?php

namespace Rennypoz\Eavquent;

use Illuminate\Support\ServiceProvider;

class EavquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publish configurations
            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('eavquent.php'),
            // ], 'config');

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'eavquent');

        // Register the main class to use with the facade
        $this->app->singleton('eavquent', function () {
            return new Eavquent;
        });
    }
}
