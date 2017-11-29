<?php

namespace TheLHC\BlacklistIp;

use Symfony\Component\HttpFoundation\IpUtils;
use Cache;

class Blacklist 
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function cloudIpsByPrefix($prefix)
    {
        return \DB::table($this->config['cloudips_table'])
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
    
}