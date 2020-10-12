<?php


namespace msamgan\FedxClient\Adapters;

use Illuminate\Http\JsonResponse;
use msamgan\FedxClient\Interfaces\AdapterInterface;

/**
 * Class FedxTrackAdapter
 * @package msamgan\FedxClient\Adapters
 */
class FedxTrackAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxTrackAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/RateService_v28.wsdl");
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
                    $fedxTrackRequest,
                    $response,
                    $executionTime
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Rates Api Hit successfully',
                'execution_time' => $executionTime,
                'execution_time_unit' => 'second',
                'package' => $response
            ]);

            /*if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
                if($response->HighestSeverity != 'SUCCESS'){
                    echo '<table border="1">';
                    echo '<tr><th>Track Reply</th><th>&nbsp;</th></tr>';
                    trackDetails($response->Notifications, '');
                    echo '</table>';
                }else{
                    if ($response->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
                        echo '<table border="1">';
                        echo '<tr><th>Shipment Level Tracking Details</th><th>&nbsp;</th></tr>';
                        trackDetails($response->CompletedTrackDetails, '');
                        echo '</table>';
                    }else{
                        echo '<table border="1">';
                        echo '<tr><th>Package Level Tracking Details</th><th>&nbsp;</th></tr>';
                        trackDetails($response->CompletedTrackDetails->TrackDetails, '');
                        echo '</table>';
                    }
                }
                printSuccess($this->client, $response);
            }else{
                printError($this->client, $response);
            }

            writeToLog($this->client);*/    // Write to log file
        } catch (\SoapFault $exception) {
            printFault($exception, $this->client);
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
            'ShipmentAccountNumber' => getProperty('trackaccount') // Replace with account used for shipment
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