<?php

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
        parent::__construct(__DIR__ . "/WSDL/RateService_v28.wsdl");
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

    public function createRequest(array $requestData)
    {
        $request['WebAuthenticationDetail'] = $this->webAuthenticationDetail();
        $request['ClientDetail'] = $this->clientDetail();
        $request['TransactionDetail'] = $this->transactionDetail();
        $request['Version'] = $this->version();

        $request['RequestedShipment'] = array(
            'ShipTimestamp' => date('c'),
            'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
            'ServiceType' => 'INTERNATIONAL_PRIORITY', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
            'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
            'Shipper' => addShipper(),
            'Recipient' => addRecipient(),
            'ShippingChargesPayment' => addShippingChargesPayment(),
            'CustomsClearanceDetail' => addCustomClearanceDetail(),
            'LabelSpecification' => addLabelSpecification(),
            'CustomerSpecifiedDetail' => array(
                'MaskedData'=> 'SHIPPER_ACCOUNT_NUMBER'
            ),
            'PackageCount' => 1,
            'RequestedPackageLineItems' => array(
                '0' => addPackageLineItem1()
            ),
            'CustomerReferences' => array(
                '0' => array(
                    'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                    'Value' => 'TC007_07_PT1_ST01_PK01_SNDUS_RCPCA_POS'
                )
            )
        );
    }

    public function invoke(array $requestData)
    {
        $fedxShippingLabelRequest = $this->createRequest($requestData);

        try{
            if(setEndpoint('changeEndpoint')){
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->processShipment($fedxShippingLabelRequest); // FedEx web service invocation

            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){
                printSuccess($this->client, $response);

                // Create PNG or PDF label
                // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
              /*  $fp = fopen(SHIP_LABEL, 'wb');
                fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
                fclose($fp);
                echo 'Label <a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';*/
            }else{
                printError($this->client, $response);
            }

            writeToLog($this->client);    // Write to log file
        } catch (SoapFault $exception) {
            printFault($exception, $this->client);
        }
    }
}
