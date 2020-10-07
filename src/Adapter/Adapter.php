<?php

namespace msamgan\FedxClient\Adapters;

class Adapter
{
    /**
     * Adapter constructor.
     */
    public function __construct()
    {
        require_once(__DIR__ . '/Commons/fedex-common.php5');
        ini_set("soap.wsdl_cache_enabled", "0");
    }
}
