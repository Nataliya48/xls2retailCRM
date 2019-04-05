<?php

Class SendRequest
{
    /**
     * @var \RetailCrm\Response\ApiResponse
     */
    private $response;

    /**
     * Подключение к CRM
     *
     * @param $urlCrm адрес CRM
     * @param $apiKey ключ API
     * @return \RetailCrm\ApiClient
     */
    private function connectionToCPM($urlCrm, $apiKey)
    {
        $client = new \RetailCrm\ApiClient(
            $urlCrm,
            $apiKey,
            \RetailCrm\ApiClient::V5
        );
        return $client;
    }

    /**
     * SendRequest constructor.
     * @param $urlCrm адрес CRM
     * @param $apiKey ключ API
     * @throws Exception
     */
    public function __construct($urlCrm, $apiKey)
    {
        try {
            $this->response = $this->connectionToCPM($urlCrm, $apiKey)->request->ordersList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        //на форме выбирается тип данных, которые загружаются в систему (заказы, клиенты, статусы или тд)
        //в зависимости от того, какой тип выбран, вызывается метод
    }


}