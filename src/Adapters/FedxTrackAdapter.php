<?php


namespace Msamgan\FedxClient\Adapters;

use Illuminate\Http\JsonResponse;
use Msamgan\FedxClient\Interfaces\AdapterInterface;

/**
 * Class FedxTrackAdapter
 * @package Msamgan\FedxClient\Adapters
 */
class FedxTrackAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxTrackAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/TrackService_v19.wsdl");
    }

    /**
     * @param array $requestData
     * @param false $log
     * @return array|JsonResponse|mixed
     */
    public function invoke(array $requestData, $log = false)
    {
        $startTime = time();
        $fedxTrackRequest = $this->createRequest($requestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->track($fedxTrackRequest);
            $executionTime = time() - $startTime;

            if ($log) {
                $this->invokeLog(
                    'trck',
                    $fedxTrackRequest,
                    $response,
                    $executionTime
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Track Api Hit successfully',
                'execution_time' => $executionTime,
                'execution_time_unit' => 'second',
                'package' => $response
            ]);
        } catch (\SoapFault $exception) {
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
        $request = $this->baseRequest();
        $request['Version'] = $this->version();

        $request['SelectionDetails'] = array(
            'PackageIdentifier' => array(
                'Type' => $requestData['type'], //'CUSTOMER_REFERENCE', 'TRACKING_NUMBER_OR_DOORTAG'
                'Value' => $requestData['value'] //getProperty('customerreference'), 'trackingnumber' // Replace with a valid customer reference
            ),
            //'ShipDateRangeBegin' => getProperty('begindate'),
            //'ShipDateRangeEnd' => getProperty('enddate'),
            'ShipmentAccountNumber' => getProperty('trackaccount'), // Replace with account used for shipment
            'ProcessingOptions' => 'INCLUDE_DETAILED_SCANS'
        );

        return $request;
    }

    /**
     * @return array|string[]
     */
    public function version()
    {
        return [
            'ServiceId' => 'trck',
            'Major' => '19',
            'Intermediate' => '0',
            'Minor' => '0'
        ];
    }
}
