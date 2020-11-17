<?php

namespace Zorb\IPay\Facades;

use Zorb\IPay\IPay as IPayService;
use Illuminate\Support\Facades\Facade;

class IPay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return IPayService::class;
    }
}
