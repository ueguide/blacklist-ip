<?php

use TheLHC\BlacklistIp\Tests\TestCase;

class BlacklistTest extends TestCase
{
    public function testMatchesCloudIpFalse()
    {
        $this->artisan('blacklist_ip:cloud_ips');
        $this->assertTrue(!Blacklist::isCloudIp('104.184.195.227'));
    }
    
    public function testMatchesCloudIpTrue()
    {
        // 104.47.169.0/24
        $this->artisan('blacklist_ip:cloud_ips');
        $this->assertTrue(Blacklist::isCloudIp('104.47.169.0'));
    }
    
    public function testMatchesCloudIpTrueReturn()
    {
        $this->artisan('blacklist_ip:cloud_ips');
        $return = Blacklist::isCloudIp('104.47.169.0', true);
        $this->assertEquals('104.47.169.0/24', $return->cidr_ip);
    }
    
    public function testBanIp()
    {
        $this->assertTrue(Blacklist::banIp('104.184.195.227'));
        $blacklist = \DB::table('blacklist_ips')->where('ip', '104.184.195.227')->first();
        $this->assertTrue(!!$blacklist);
    }
    
    public function testIsBlacklistIp()
    {
        Blacklist::banIp('104.184.195.227');
        $this->assertTrue(Blacklist::isBlacklistIp('104.184.195.227'));
    }
    
    public function testUnBanIp()
    {
        Blacklist::banIp('104.184.195.227');
        $this->assertTrue(Blacklist::unBanIp('104.184.195.227'));
        $blacklist = \DB::table('blacklist_ips')->where('ip', '104.184.195.227')->first();
        $this->assertTrue(!$blacklist);
    }
}