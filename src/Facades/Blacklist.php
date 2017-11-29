<?php

namespace TheLHC\BlacklistIp\Facades;

use Illuminate\Support\Facades\Facade;

class Blacklist extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'blacklist';
    }
}
