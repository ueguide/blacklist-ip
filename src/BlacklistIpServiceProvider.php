<?php

namespace TheLHC\BlacklistIp;

use Illuminate\Support\ServiceProvider;
use TheLHC\BlacklistIp\Console\MigrationCommand;

class BlacklistIpServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        /*
        $this->publishes([
            __DIR__ . '/../config/blacklist_ip.php' => config_path('blacklist_ip.php')
        ], 'config');
        */
       
        $this->commands([
            MigrationCommand::class
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        /*
        $this->mergeConfigFrom(__DIR__ . '/../config/blacklist_ip.php', 'blacklist_ip');
        */
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
