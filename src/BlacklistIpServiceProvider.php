<?php

namespace TheLHC\BlacklistIp;

use Illuminate\Support\ServiceProvider;
use TheLHC\BlacklistIp\Console\MigrationCommand;
use TheLHC\BlacklistIp\Console\UpdateCloudIps;

class BlacklistIpServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/blacklist_ip.php' => config_path('blacklist_ip.php')
        ], 'config');

        if (! class_exists('CreateBlacklistIpsTable')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../migrations/create_blacklist_ips_table.php' => database_path("/migrations/{$timestamp}_create_blacklist_ips_table.php"),
            ], 'migrations');
        }

        if (! class_exists('CreateCloudipsTable')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../migrations/create_cloudips_table.php' => database_path("/migrations/{$timestamp}_create_cloudips_table.php"),
            ], 'migrations');
        }


        $this->app->singleton(
            'blacklist_ip.update_cloud_ips',
            function($app) {
                return new UpdateCloudIps(
                    $app->make('config')->get('blacklist_ip')
                );
            }
        );

        /*
        $this->commands([
            MigrationCommand::class,
            'blacklist_ip.update_cloud_ips'
        ]);
        */
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blacklist_ip.php', 'blacklist_ip');

        $this->app->singleton('blacklist', function ($app) {
            return new Blacklist($app['config']->get('blacklist_ip'));
        });
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
