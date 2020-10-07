<?php

use Illuminate\Support\Facades\Log;
use msamgan\FedxClient\Adapters\Adapter;

/**
 * Class FedexShippingLabelAdapter
 */
class FedexShippingLabelAdapter extends Adapter
{
    /**
     * @var SoapClient
     */
    protected $client;

    /**
     * FedexShippingLabelAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $path_to_wsdl = __DIR__ . "/RateService_v28.wsdl";

        try {
            $this->client = new SoapClient($path_to_wsdl, array('trace' => 1));
            // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
        } catch (SoapFault $e) {
            Log::error($e->getMessage());
        }
    }
}
