<?php

namespace Msamgan\FedxClient\Adapters;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Msamgan\FedxClient\Adapters\Adapter;
use Msamgan\FedxClient\Interfaces\AdapterInterface;
use Msamgan\FedxClient\Models\FedxLog;
use SoapFault;

/**
 * Class FedxRatesAdapter
 * @package Msamgan\FedxClient\Adapters
 */
class FedxRatesAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxRatesAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/RateService_v28.wsdl");
    }

    /**
     * @param array $fedxRateRequestData
     * @param bool $log
     * @return array|JsonResponse|mixed
     */
    public function invoke(array $fedxRateRequestData, $log = true)
    {
        $startTime = time();
        $fedxRateRequest = $this->createRequest($fedxRateRequestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->getRates($fedxRateRequest);
            $executionTime = time() - $startTime;

            $logData = null;
            if ($log) {
                $logData = $this->invokeLog(
                    'crs',
                    $fedxRateRequest,
                    $response,
                    $executionTime
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Rates Api Hit successfully',
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
     * @param array $fedxRateRequestData
     * @return array|mixed
     */
    public function createRequest(array $fedxRateRequestData)
    {
        $request = $this->baseRequest();
        $request['Version'] = $this->version();

        $request['ReturnTransitAndCommit'] = true;
        $request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
        $request['RequestedShipment']['ShipTimestamp'] = date('c'); // Service Type and Packaging Type are not passed in the request

        $request['RequestedShipment']['Shipper'] = array(
            'Address' => $fedxRateRequestData['shipper_address']
        );

        $request['RequestedShipment']['Recipient'] = array(
            'Address' => $fedxRateRequestData['recipient_address']
        );

        $request['RequestedShipment']['ShippingChargesPayment'] = array(
            'PaymentType' => 'SENDER',
            'Payor' => array(
                'ResponsibleParty' => array(
                    'AccountNumber' => getProperty('billaccount'),
                    'Contact' => null,
                    'Address' => array(
                        'CountryCode' => 'US'
                    )
                )
            )
        );

        $request['RequestedShipment']['PackageCount'] = count($fedxRateRequestData['parcels']);
        foreach ($fedxRateRequestData['parcels'] as $key => $parcel) {
            $request['RequestedShipment']['RequestedPackageLineItems'][$key] = [
                'SequenceNumber' => ($key + 1),
                'GroupPackageCount' => ($key + 1),
                'Weight' => $parcel['weight'],
                'Dimensions' => $parcel['dimensions']
            ];
        }

        return $request;
    }

    /**
     * @return array|string[]
     */
    public function version()
    {
        return [
            'ServiceId' => 'crs',
            'Major' => '28',
            'Intermediate' => '0',
            'Minor' => '0'
        ];
    }
}
