<?php

use TheLHC\BlacklistIp\Tests\TestCase;

class MigrationsTest extends TestCase
{
    public function testRunningMigrations()
    {
        $count = \DB::table('cloudips')->count();
        $this->assertEquals(0, $count);
        
        $count = \DB::table('blacklist_ips')->count();
        $this->assertEquals(0, $count);
    }
}