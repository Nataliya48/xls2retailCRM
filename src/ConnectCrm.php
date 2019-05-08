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

    /**
     * Получаем список полей CRM
     *
     * @return mixed
     */
    public function listFields()
    {
        $listFields = json_decode(file_get_contents(__DIR__ . '/retailcrm.json'), true);
        return array_push($listFields['orders'], ['customFields' => $this->customFields()]);
    }

    /**
     * Возвращает список пользовательских полей CRM
     *
     * @return array
     */
    private function customFields()
    {
        $fields = $this->connectionToCrm()->request->customFieldsList();
        $customFields = [];
        foreach ($fields as $field){
            $customFields[$field['code']] = $field['name'];
        }
        return $customFields;
    }
}