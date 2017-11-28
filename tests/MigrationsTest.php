<?php

use TheLHC\BlacklistIp\Tests\TestCase;

class MigrationsTest extends TestCase
{
    public function testRunningMigrations()
    {
        //$users = \DB::table('testbench_users')->where('id', '=', 1)->first();
        //$this->assertEquals('hello@orchestraplatform.com', $users->email);
        //$this->assertTrue(\Hash::check('123', $users->password));
        $count = \DB::table('cloudips')->count();
        $this->assertEquals(0, $count);
    }
}