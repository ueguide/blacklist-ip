<?php

namespace TheLHC\BlacklistIp\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use TheLHC\BlacklistIp\BlacklistIpServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;


class TestCase extends BaseTestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        foreach(glob(database_path('migrations').'/*.php') as $mig) {
            unlink($mig);
        }
        $this->artisan('blacklist_ip:migration');
        
        $this->loadMigrationsFrom([
            '--database' => 'testbench'
        ]);

    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);

    }

    /**
     * Get package service providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Orchestra\Database\ConsoleServiceProvider::class,
            BlacklistIpServiceProvider::class
        ];
    }
    
    protected function getPackageAliases($app)
    {
        return [
            'Blacklist' => 'TheLHC\BlacklistIp\Facades\Blacklist'
        ];
    }

}
