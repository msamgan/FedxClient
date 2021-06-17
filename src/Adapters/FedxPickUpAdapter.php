<?php


namespace Msamgan\FedxClient\Adapters;


use Msamgan\FedxClient\Interfaces\AdapterInterface;

class FedxPickUpAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxPickUpAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/RateService_v28.wsdl");
    }

    /**
     * @return array
     */
    public function version()
    {
        // TODO: Implement version() method.
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function createRequest(array $requestData)
    {
        $request = $this->baseRequest();

        $request['OriginDetail'] = array(
            'PickupLocation' => array(
                'Contact' => array(
                    'PersonName' => 'Contact Name',
                    'CompanyName' => 'Company Name',
                    'PhoneNumber' => '1234567890'
                ),
                'Address' => array(
                    'StreetLines' => array('Address Line 1'),
                    'City' => 'Foster City',
                    'StateOrProvinceCode' => 'CA',
                    'PostalCode' => '94404',
                    'CountryCode' => 'US')
            ),
            'PackageLocation' => 'FRONT', // valid values NONE, FRONT, REAR and SIDE
            'BuildingPartCode' => 'SUITE', // valid values APARTMENT, BUILDING, DEPARTMENT, SUITE, FLOOR and ROOM
            'BuildingPartDescription' => '3B',
            'ReadyTimestamp' => getProperty('pickuptimestamp'), // Replace with your ready date time
            'CompanyCloseTime' => '20:00:00'
        );
        $request['PackageCount'] = '1';
        $request['TotalWeight'] = array(
            'Value' => '1.0',
            'Units' => 'LB' // valid values LB and KG
        );
        $request['CarrierCode'] = 'FDXE';
        // valid values FDXE-Express, FDXG-Ground, FDXC-Cargo, FXCC-Custom Critical and FXFR-Freight
        //$request['OversizePackageCount'] = '1';
        $request['CourierRemarks'] = 'This is a test.  Do not pickup';

        return $request;
    }

    /**
     * @param array $requestData
     * @param false $log
     * @return mixed|void
     */
    public function invoke(array $requestData, $log = false)
    {
        $startTime = time();
        $fedxPickUpRequest = $this->createRequest($requestData);

        try {
            if(setEndpoint('changeEndpoint')){
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client ->createPickup($fedxPickUpRequest);
            $executionTime = time() - $startTime;

            $logData = null;
            if ($log) {
                $logData = $this->invokeLog(
                    'pickup',
                    $fedxPickUpRequest,
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

            /*if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
                echo 'Pickup confirmation number is: '.$response -> PickupConfirmationNumber .Newline;
                echo 'Location: '.$response -> Location .Newline;
                printSuccess($this->client, $response);
            }else{
                printError($this->client, $response);
            }

            writeToLog($this->client);*/   // Write to log file
        } catch (\SoapFault $exception) {
            printFault($exception, $this->client);
            return [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
