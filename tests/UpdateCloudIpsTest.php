<?php

use TheLHC\BlacklistIp\Tests\TestCase;

class UpdateCloudIpsTest extends TestCase
{
    public function testUpdateCloudIps()
    {
        $this->artisan('blacklist_ip:cloud_ips');
        $awsIp = \DB::table('cloudips')->where('source', 'Amazon Web Services')->first();
        $azureIp = \DB::table('cloudips')->where('source', 'Microsoft Azure')->first();
        $this->assertTrue(!empty($awsIp));
        $this->assertTrue(!empty($azureIp));
    }
}