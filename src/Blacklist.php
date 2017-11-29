<?php

namespace TheLHC\BlacklistIp;

use Symfony\Component\HttpFoundation\IpUtils;
use Carbon\Carbon;
use Cache;
use DB;

class Blacklist 
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function cloudIpsByPrefix($prefix)
    {
        return DB::table($this->config['cloudips_table'])
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
        $cloudIps = Cache::remember("cloudip-{$prefix}", 60, function() use ($prefix) {
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
    
    public function banIp($ip)
    {
        if (is_null($ip)) return false;
        $timestamp = new Carbon;
        $attrs = [
            'ip'         => $ip,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];
        $record = DB::table($this->config['blacklist_table'])
                    ->where('ip', $ip)
                    ->first();
        if ($record) {
            unset($attrs['created_at']);
            DB::table($this->config['blacklist_table'])
                ->where('ip', $ip)
                ->update($attrs);
        } else {
            DB::table($this->config['blacklist_table'])->insert($attrs);
        }
        
        return true;
    }
    
    public function isBlacklistIp($ip)
    {
        // get ip subnet
        $ip_net = explode('.', $ip);
        array_pop($ip_net);
        $ipSubnet = implode('.', $ip_net).'.';
        // find a strict matching IP or subnet
        $blacklisted = DB::table($this->config['blacklist_table'])
                            ->whereRaw("ip = ? OR ip = ?", [$ip, $ipSubnet])
                            ->first();
        
        return !!$blacklisted;
    }
    
    public function unBanIp($ip)
    {
        $record = DB::table($this->config['blacklist_table'])
                    ->where('ip', $ip)
                    ->first();
        if ($record) {
            DB::table($this->config['blacklist_table'])
                ->where('ip', $ip)
                ->delete();
        }
        
        return true;
    }
    
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