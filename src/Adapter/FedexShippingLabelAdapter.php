<?php

namespace msamgan\FedxClient\Adapters;

use msamgan\FedxClient\Adapters\Adapter;
use msamgan\FedxClient\Interfaces\AdapterInterface;

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

    public function invoke(array $requestData)
    {
        $startTime = time();
        $fedxShippingLabelRequest = $this->createRequest($requestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->processShipment($fedxShippingLabelRequest); // FedEx web service invocation
            $executionTime = time() - $startTime;

            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                //printSuccess($this->client, $response);

                return response()->json([
                    'status' => true,
                    'message' => 'Rates Api Hit successfully',
                    'execution_time' => $executionTime,
                    'execution_time_unit' => 'second',
                    'package' => $response
                ]);

                // Create PNG or PDF label
                // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
                /*  $fp = fopen(SHIP_LABEL, 'wb');
                  fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
                  fclose($fp);
                  echo 'Label <a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';*/
            } else {
                printError($this->client, $response);
            }
            //writeToLog($this->client);    // Write to log file
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
