<?php

namespace Export;

class ConnectCrm
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
     * @param $url string адрес CRM
     * @param $apiKey string ключ API
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

    /**
     * Получаем список полей CRM
     *
     * @return array
     */
    public function listFields(): array
    {
        $listFields = json_decode(file_get_contents(__DIR__ . '/retailcrm.json'), true);
        return $listFields;
    }

    /**
     * Возвращает список пользовательских полей CRM
     *
     * @return array
     */
    public function customFields()
    {
        $fields = $this->connectionToCrm()->request->customFieldsList()->customFields;
        $customFields = [];
        foreach ($fields as $field){
            $customFields[$field['code']] = $field['name'];
        }
        return $customFields;
    }
}