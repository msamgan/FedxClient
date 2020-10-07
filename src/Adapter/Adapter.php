<?php

namespace msamgan\FedxClient\Adapters;

use Illuminate\Support\Facades\Log;

/**
 * Class Adapter
 * @package msamgan\FedxClient\Adapters
 */
abstract class Adapter
{
    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * Adapter constructor.
     * @param $pathToWsdl
     */
    public function __construct($pathToWsdl)
    {
        ini_set("soap.wsdl_cache_enabled", "0");

        try {
            $this->client = new \SoapClient($pathToWsdl, array('trace' => 1));
            // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
        } catch (\SoapFault $e) {
            Log::error($e->getMessage());
        }
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
