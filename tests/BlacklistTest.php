<?php

use TheLHC\BlacklistIp\Events\IpUnBanned;
use TheLHC\BlacklistIp\Events\IpBanned;
use TheLHC\BlacklistIp\Tests\TestCase;
use Illuminate\Support\Facades\Event;

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
        Event::fake();

        $ip = '104.184.195.227';

        $this->assertTrue(Blacklist::banIp($ip));
        Event::assertDispatched(IpBanned::class, function ($event) use ($ip) {
            return $event->ip === $ip;
        });
        $blacklist = \DB::table('blacklist_ips')->where('ip', $ip)->first();
        $this->assertTrue(!!$blacklist);
    }
    
    public function testIsBlacklistIp()
    {
        Blacklist::banIp('104.184.195.227');
        $this->assertTrue(Blacklist::isBlacklistIp('104.184.195.227'));
    }
    
    public function testUnBanIp()
    {
        Event::fake();

        $ip = '104.184.195.227';

        Blacklist::banIp($ip);
        $this->assertTrue(Blacklist::unBanIp($ip));
        Event::assertDispatched(IpUnBanned::class, function ($e) use ($ip) {
            return $e->ip === $ip;
        });
        $blacklist = \DB::table('blacklist_ips')->where('ip', $ip)->first();
        $this->assertTrue(!$blacklist);
    }
    
    public function testShouldIgnoreIp()
    {
        Blacklist::banIp('104.184.195.227');
        $this->assertTrue(Blacklist::shouldIgnoreIp('104.184.195.227'));
    }
}
