<?php

namespace msamgan\FedxClient\Adapters;


use Illuminate\Support\Facades\Log;
use msamgan\FedxClient\Adapters\Adapter;
use msamgan\FedxClient\Interfaces\AdapterInterface;

/**
 * Class FedxRatesAdapter
 * @package msamgan\FedxClient\Adapters
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

    /**
     * @param array $fedxRateRequestData
     * @return array|mixed
     */
    public function createRequest(array $fedxRateRequestData)
    {
        $request['WebAuthenticationDetail'] = $this->webAuthenticationDetail();
        $request['ClientDetail'] = $this->clientDetail();
        $request['TransactionDetail'] = $this->transactionDetail();
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
     * @param array $fedxRateRequestData
     * @return array|mixed
     */
    public function invoke(array $fedxRateRequestData)
    {
        $fedxRateRequest = $this->createRequest($fedxRateRequestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            return [
                'status' => true,
                'message' => 'Rates Api Hit successfully',
                'package' => $this->client->getRates($fedxRateRequest)
            ];

        } catch (SoapFault $exception) {
            printFault($exception, $this->client);
            return [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
