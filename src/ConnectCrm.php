<?php

namespace Export;

class ConnectCrm
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
     * Подключение к CRM
     *
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

    /**
     * ConnectCrm constructor.
     * @param $url адрес CRM
     * @param $apiKey ключ API
     */
    public function __construct($url, $apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    /**
     * Получение списка сайтов
     *
     * @return array
     */
    public function getSiteName()
    {
        $siteNames = $this->connectionToCrm()->request->sitesList();
        $arrNames = [];
        foreach ($siteNames['sites'] as $name) {
            $arrNames[$name['code']] = $name['name'];
        }
        return $arrNames;
    }

    public function listFields()
    {
        return json_decode(file_get_contents(__DIR__ . '/retailcrm.json'), true);
    }
}