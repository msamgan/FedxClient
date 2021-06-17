<?php

namespace Msamgan\FedxClient\Adapters;

use Illuminate\Http\JsonResponse;
use Msamgan\FedxClient\Adapters\Adapter;
use Msamgan\FedxClient\Interfaces\AdapterInterface;
use SoapFault;

/**
 * Class FedexShippingLabelAdapter
 */
class FedexShippingLabelAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedexShippingLabelAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/ShipService_v26.wsdl");
    }

    /**
     * @param array $requestData
     * @param bool $log
     * @return array|JsonResponse|mixed
     */
    public function invoke(array $requestData, $log = true)
    {
        $startTime = time();
        $fedxShippingLabelRequest = $this->createRequest($requestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->processShipment($fedxShippingLabelRequest);
            $executionTime = time() - $startTime;

            $logData = null;
            if ($log) {
                $logData = $this->invokeLog(
                    'ship',
                    $fedxShippingLabelRequest,
                    $response,
                    $executionTime
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Shippment Label Api Hit successfully',
                'execution_time' => $executionTime,
                'execution_time_unit' => 'second',
                'log' => $logData,
                'package' => $response
            ]);

        } catch (SoapFault $exception) {
            return [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param array $requestData
     * @return array|mixed
     */
    public function createRequest(array $requestData)
    {
        $request['WebAuthenticationDetail'] = $this->webAuthenticationDetail();
        $request['ClientDetail'] = $this->clientDetail();
        $request['TransactionDetail'] = $this->transactionDetail();
        $request['Version'] = $this->version();

        $request['RequestedShipment'] = $requestData;

        return $request;
    }

    /**
     * @return array|string[]
     */
    public function version()
    {
        return [
            'ServiceId' => 'ship',
            'Major' => '26',
            'Intermediate' => '0',
            'Minor' => '0'
        ];
    }
}
