<?php


namespace Msamgan\FedxClient\Adapters;


use Illuminate\Http\JsonResponse;
use Msamgan\FedxClient\Interfaces\AdapterInterface;

class FedxHsCodeAdapter extends Adapter implements AdapterInterface
{
    /**
     * FedxHsCodeAdapter constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . "/WSDL/TradeToolsService_v1.wsdl");
    }

    /**
     * @param array $requestData
     * @param bool $log
     * @return array|JsonResponse|mixed
     */
    public function invoke(array $requestData, $log = true)
    {
        $startTime = time();
        $fedxHsCodeRequest = $this->createRequest($requestData);

        try {
            if (setEndpoint('changeEndpoint')) {
                $newLocation = $this->client->__setLocation(setEndpoint('endpoint'));
            }

            $response = $this->client->hsSearch($fedxHsCodeRequest);

            $executionTime = time() - $startTime;

            if ($log) {
                $this->invokeLog(
                    'hssearch',
                    $fedxHsCodeRequest,
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
            dump($exception);
            return [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }

        // TODO: Implement invoke() method.
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function createRequest(array $requestData)
    {
        $request = $this->baseRequest();
        $request['Version'] = $this->version();
        $request['Features'] = 'HS_SEARCH';
        $request['ResultsToSkip'] = 0;
        $request['ResultsRequested'] = 5000;
        $request['SearchType'] = $requestData['SearchType']; //CODE, DESCRIPTION
        $request['SearchFilter'] = $requestData['SearchFilter'];
        $request['SearchText'] = $requestData['SearchText'];
        $request['DestinationCountry'] = $requestData['DestinationCountry'];
        $request['ImportDate'] = date('Y-m-d');

        return $request;
    }

    /**
     * @return array
     */
    public function version()
    {
        return [
            'ServiceId' => 'trdt',
            'Major' => '1',
            'Intermediate' => '0',
            'Minor' => '0'
        ];
    }
}
