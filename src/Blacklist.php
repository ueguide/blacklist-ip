<?php

namespace TheLHC\BlacklistIp;

use Symfony\Component\HttpFoundation\IpUtils;
use TheLHC\BlacklistIp\Events\IpUnBanned;
use TheLHC\BlacklistIp\Events\IpBanned;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class Blacklist
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve IPs grouped by a prefix
     *
     * @param string $prefix
     * @return object
     */
    public function cloudIpsByPrefix($prefix)
    {
        return app('db')->table($this->config['cloudips_table'])
                    ->where('cidr_ip', 'like', "{$prefix}.%")
                    ->get();
    }

    /**
     * Test IP against cloud IP database
     *
     * @param  String  $ip         ip address
     * @param  boolean $returnInfo if true, return matching CloudIp instance (contains meta data)
     * @return boolean             returns instances if $returnInfo = true
     */
    public function isCloudIp($ip, $returnInfo = false)
    {
        list($prefix) = explode('.', $ip);
        // if ip=123.4.5.6, only test ips matching 123.%
        $cloudIps = app('cache')->remember("cloudip-{$prefix}", 60, function() use ($prefix) {
            return self::cloudIpsByPrefix($prefix);
        });
        // check for match
        foreach ($cloudIps as $cloudIp) {
            if (IpUtils::checkIp($ip, $cloudIp->cidr_ip)) {
                if ($returnInfo) {
                    return $cloudIp;
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ban an IP
     *
     * @param string $ip
     * @return boolean
     */
    public function banIp($ip)
    {
        if (is_null($ip)) return false;
        $timestamp = new Carbon;
        $attrs = [
            'ip'         => $ip,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];
        $record = app('db')->table($this->config['blacklist_table'])
                    ->where('ip', $ip)
                    ->first();
        if ($record) {
            unset($attrs['created_at']);
            app('db')->table($this->config['blacklist_table'])
                ->where('ip', $ip)
                ->update($attrs);
        } else {
            app('db')->table($this->config['blacklist_table'])->insert($attrs);
        }

        Event::dispatch(new IpBanned($ip));

        return true;
    }

    /**
     * Check if the IP is blacklisted
     *
     * @param string $ip
     * @return boolean
     */
    public function isBlacklistIp($ip)
    {
        // get ip subnet
        $ip_net = explode('.', $ip);
        array_pop($ip_net);
        $ipSubnet = implode('.', $ip_net).'.';
        // find a strict matching IP or subnet
        $blacklisted = app('db')->table($this->config['blacklist_table'])
                            ->whereRaw("ip = ? OR ip = ?", [$ip, $ipSubnet])
                            ->first();

        return !!$blacklisted;
    }

    /**
     * Unban an IP
     *
     * @param string $ip
     * @return boolean
     */
    public function unBanIp($ip)
    {
        $record = app('db')->table($this->config['blacklist_table'])
                    ->where('ip', $ip)
                    ->first();
        if ($record) {
            app('db')->table($this->config['blacklist_table'])
                ->where('ip', $ip)
                ->delete();
        }

        Event::dispatch(new IpUnBanned($ip));

        return true;
    }

    /**
     * Check if the IP shoudld be ignored
     *
     * @param string $ip
     * @return boolean
     */
    public function shouldIgnoreIp($ip)
    {
        return ($this->isBlacklistIp($ip) or $this->isCloudIp($ip));
    }

    /**
     * Check if client is human by User-Agent
     * @param  String  $agent
     * @return boolean
     */
    public function isHuman($agent)
    {
        return (
            !preg_match('#(bot|google|crawler|spider|prerender|facebookexternalhit)#i', $agent) and
            !is_null($agent)
        );
    }

}
