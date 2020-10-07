<?php

namespace msamgan\FedxClient\Adapters;


use Illuminate\Support\Facades\Log;
use msamgan\FedxClient\Adapters\Adapter;

/**
 * Class FedxRatesAdapter
 * @package msamgan\FedxClient\Adapters
 */
class FedxRatesAdapter extends Adapter
{
    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * FedxRatesAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $path_to_wsdl = __DIR__ . "/WSDL/RateService_v28.wsdl";
        try {
            $this->client = new \SoapClient($path_to_wsdl, array('trace' => 1));
            // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
        } catch (\SoapFault $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $fedxRateRequestData
     * @return mixed
     */
    public function createRateRequest($fedxRateRequestData)
    {
        /**
         * Fetched from Env.
         */
        $request['WebAuthenticationDetail'] = array(
            'ParentCredential' => array(
                'Key' => getProperty('parentkey'),
                'Password' => getProperty('parentpassword')
            ),
            'UserCredential' => array(
                'Key' => getProperty('key'),
                'Password' => getProperty('password')
            )
        );

        /**
         * Fetched from Env.
         */
        $request['ClientDetail'] = array(
            'AccountNumber' => getProperty('shipaccount'),
            'MeterNumber' => getProperty('meter')
        );


        $request['TransactionDetail'] = array('CustomerTransactionId' => time());
        $request['Version'] = array(
            'ServiceId' => 'crs',
            'Major' => '28',
            'Intermediate' => '0',
            'Minor' => '0'
        );
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

        /*$request['RequestedShipment']['RequestedPackageLineItems'] = array(
            '0' => array(
                'SequenceNumber' => 1,
                'GroupPackageCount' => 1,
                'Weight' => array(
                    'Value' => 2.0,
                    'Units' => 'LB'
                ),
                'Dimensions' => array(
                    'Length' => 2,
                    'Width' => 2,
                    'Height' => 2,
                    'Units' => 'IN'
                )
            ),
            '1' => array(
                'SequenceNumber' => 2,
                'GroupPackageCount' => 1,
                'Weight' => array(
                    'Value' => 5.0,
                    'Units' => 'LB'
                ),
                'Dimensions' => array(
                    'Length' => 20,
                    'Width' => 20,
                    'Height' => 10,
                    'Units' => 'IN'
                )
            )
        );*/

        return $request;
    }

    /**
     * @param $fedxRateRequestData
     * @return array|string
     */
    public function getRates($fedxRateRequestData)
    {
        $fedxRateRequest = $this->createRateRequest($fedxRateRequestData);

        //dd($fedxRateRequest);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            dd($this->client->getRates($fedxRateRequest));

            return [
                'status' => true,
                'message' => 'Rates Api Hit successfully',
                'package' => $this->client->getRates($fedxRateRequest)
            ];

            /*if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {

                return $response;

                echo 'Rates for following service type(s) were returned.' . Newline . Newline;
                echo '<table border="1">';
                echo '<tr><td>Service Type</td><td>Amount</td><td>Delivery Date</td>';
                if (is_array($response->RateReplyDetails)) {
                    foreach ($response->RateReplyDetails as $rateReply) {
                        $this->printRateReplyDetails($rateReply);
                    }
                } else {
                    $this->printRateReplyDetails($response->RateReplyDetails);
                }
                echo '</table>' . Newline;
                printSuccess($this->client, $response);

                return $response;
            } else {
                printError($this->client, $response);
            }

            writeToLog($this->client);*/    // Write to log file
        } catch (SoapFault $exception) {
            printFault($exception, $this->client);
            return [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    /*public function printRateReplyDetails($rateReply)
    {
        echo '<tr>';
        $serviceType = '<td>' . $rateReply->ServiceType . '</td>';
        if ($rateReply->RatedShipmentDetails && is_array($rateReply->RatedShipmentDetails)) {
            $amount = '<td>$' . number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2, ".", ",") . '</td>';
        } elseif ($rateReply->RatedShipmentDetails && !is_array($rateReply->RatedShipmentDetails)) {
            $amount = '<td>$' . number_format($rateReply->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount, 2, ".", ",") . '</td>';
        }
        if (property_exists($rateReply, 'DeliveryTimestamp')) {
            $deliveryDate = '<td>' . $rateReply->DeliveryTimestamp . '</td>';
        } else {
            $deliveryDate = '<td>' . $rateReply->TransitTime . '</td>';
        }
        echo $serviceType . $amount . $deliveryDate;
        echo '</tr>';
    }*/
}
