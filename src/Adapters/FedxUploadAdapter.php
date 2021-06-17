<?php


namespace Msamgan\FedxClient\Adapters;

use Illuminate\Http\JsonResponse;
use Msamgan\FedxClient\Interfaces\AdapterInterface;

/**
 * Class FedxUploadAdapter
 * @package Msamgan\FedxClient\Adapters
 */
class FedxUploadAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxTrackAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/UploadDocumentService_v17.wsdl");
    }

    /**
     * @param array $requestData
     * @param bool $log
     * @return array|JsonResponse
     */
    public function invoke(array $requestData, $log = true)
    {
        $startTime = time();
        $fedxUploadDocRequest = $this->createRequest($requestData);

        dd($fedxUploadDocRequest);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->uploadDocuments($fedxUploadDocRequest);
            $executionTime = time() - $startTime;

            if ($log) {
                $this->invokeLog(
                    'cdus',
                    $fedxUploadDocRequest,
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

        $request['OriginCountryCode'] = $requestData['OriginCountryCode'];
        $request['DestinationCountryCode'] = $requestData['DestinationCountryCode'];

        $request['Documents'] = $requestData['Documents'];

        return $request;
    }

    /**
     * @return array|string[]
     */
    public function version(): array
    {
        return [
            'ServiceId' => 'cdus',
            'Major' => '17',
            'Intermediate' => '0',
            'Minor' => '0'
        ];
    }
}
