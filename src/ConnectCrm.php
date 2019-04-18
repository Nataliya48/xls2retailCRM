<?php

Class ConnectCrm
{
    /**
     * адрес retailCRM
     * @var
     */
    private $url;
    /**
     * API ключ
     * @var
     */
    private $apiKey;
    /**
     * символьный код сайта
     * @var
     */
    private $site;

    /**
     * Подключение к CRM
     *
     * @param $url адрес CRM
     * @param $apiKey ключ API
     * @return \RetailCrm\ApiClient
     */
    private function connectionToCrm()
    {
        $client = new \RetailCrm\ApiClient(
            $this->url,
            $this->apiKey,
            \RetailCrm\ApiClient::V5
        );
        return $client;
    }

    public function __construct($url, $apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    public function getSite()
    {
        return $this->connectionToCrm()->request->credentials();
    }
}