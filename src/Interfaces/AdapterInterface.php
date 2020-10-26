<?php

namespace msamgan\FedxClient\Interfaces;

interface AdapterInterface
{
    /**
     * @return array
     * current version of the api call.
     */
    public function version();

    /**
     * @param array $requestData
     * @return array
     * all the data required to create a request is provided in a array.
     */
    public function createRequest(array $requestData);

    /**
     * @param array $requestData
     * @param bool $log
     * @return mixed
     * invoke the adapter api.
     */
    public function invoke(array $requestData, $log = true);
}
