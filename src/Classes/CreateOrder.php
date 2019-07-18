<?php

namespace Query;

class CreateOrder
{
    /**
     * адрес retailCRM
     * @var string
     */
    private $url;

    /**
     * API ключ
     * @var string
     */
    private $apiKey;

    /**
     * Массив заказа в формате json
     * @var string
     */
    private $order;

    /**
     * Сайт retailCRM
     * @var string
     */
    private $site;

    /**
     * Подключение к CRM
     *
     * @return \RetailCrm\ApiClient
     */
    private function connectToCrm()
    {
        $client = new \RetailCrm\ApiClient(
            $this->url,
            $this->apiKey,
            \RetailCrm\ApiClient::V5
        );
        return $client;
    }

    /**
     * CreateOrder constructor.
     * @param $url string адрес CRM
     * @param $apiKey string ключ API
     * @param $order string массив заказа в формате json
     * @throws Exception
     */
    public function __construct($url, $apiKey, $order, $site)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        $this->site = $site;
        $this->order = $order;
    }

    /**
     * @return array ответ от CRM
     * @throws Exception
     */
    public function ordersCreate()
    {
        try {
            $response = $this->connectToCrm()->request->ordersCreate([$this->order], $this->site);
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        if (!$response->isSuccessful()){
            return [
                'code' => $response->getStatusCode(),
                'msg' => $response->getErrorMsg(),
                'error' => isset($response['errors']) ? $response['errors'] : 'not errors'
            ];
        } else {
            return [
                'code' => $response->getStatusCode(),
                'orders' => $this->order
            ];
        }
    }
}