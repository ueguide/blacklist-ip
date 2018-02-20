<?php

namespace TheLHC\BlacklistIp\Events;

class IpUnBanned
{
    public $ip;

    /**
     * Create a new event instance
     *
     * @param string $ip
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }
}

