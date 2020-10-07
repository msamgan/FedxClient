<?php

namespace msamgan\FedxClient\Adapters;

class Adapter
{
    /**
     * Adapter constructor.
     */
    public function __construct()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
    }

    /**
     * @return array[]
     * Fetched from Env.
     */
    public function webAuthenticationDetail()
    {
        return [
            'ParentCredential' => [
                'Key' => getProperty('parentkey'),
                'Password' => getProperty('parentpassword')
            ],
            'UserCredential' => [
                'Key' => getProperty('key'),
                'Password' => getProperty('password')
            ]
        ];
    }

    /**
     * @return array
     * Fetched from Env.
     */
    public function clientDetail()
    {
        return [
            'AccountNumber' => getProperty('shipaccount'),
            'MeterNumber' => getProperty('meter')
        ];
    }

    /**
     * @return array
     */
    public function transactionDetail()
    {
        return [
            'CustomerTransactionId' => time(),
            'CustomerTransactionTimeStamp' => now()
        ];
    }
}
