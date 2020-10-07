<?php

use Illuminate\Support\Facades\Log;
use msamgan\FedxClient\Adapters\Adapter;

/**
 * Class FedexShippingLabelAdapter
 */
class FedexShippingLabelAdapter extends Adapter implements \msamgan\FedxClient\Interfaces\AdapterInterface
{
    /**
     * FedexShippingLabelAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/RateService_v28.wsdl");
    }

    public function version()
    {
        // TODO: Implement version() method.
    }

    public function createRequest(array $requestData)
    {
        // TODO: Implement createRequest() method.
    }

    public function acquire(array $requestData)
    {
        // TODO: Implement acquire() method.
    }
}
