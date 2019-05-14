<?php

namespace Export;

Class SendRequest
{
    /**
     * Массив заказов
     *
     * @var array
     */
    private $table;

    /**
     * Адрес retailCRM
     *
     * @var string
     */
    private $url;

    /**
     * API ключ
     *
     * @var string
     */
    private $apiKey;

    /**
     * Поля заказов из CRM
     *
     * @var array
     */
    private $fieldsCrm;

    /**
     * Поля заказов из файла xls
     *
     * @var array
     */
    private $fieldsFile;

    /**
     * Подключение к CRM
     *
     * @param $urlCrm адрес CRM
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

    /**
     * SendRequest constructor.
     * @param $urlCrm адрес CRM
     * @param $apiKey ключ API
     * @throws Exception
     */
    public function __construct($url, $apiKey, $table, $fieldsCrm, $fieldsFile)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        unset($table[0]);
        $this->table = $table;
        $this->fieldsCrm = $fieldsCrm;
        $this->fieldsFile = $fieldsFile;

        //'car' => 'fast'
        //array_search("car",array_keys($a)); = 1
    }

    /**
     * Формирование массива для отправки
     *
     * @return array
     */
    public function assemblyOrder()
    {
        $assemblyOrderCrm = [];
        foreach ($this->table as $order){
            $orderCrm = [];
            foreach ($order as $keyFieldFile => $field){
                $keyFieldCrm = array_search($keyFieldFile, array_keys($this->fieldsCrm));
                $orderCrm[$keyFieldCrm] = $field;
            }
            $assemblyOrderCrm[] = $orderCrm;
        }
        return $assemblyOrderCrm;
    }

    /**
     * Получить список символьных кодов статусов из CRM
     *
     * @return array
     */
    private function getListStatusCode()
    {
        try {
            $response = $this->connectionToCrm()->request->statusesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $statusCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->statuses as $status){
                $statusCodeList[$status['code']] = $status['name'];
            }
        } else {
            $this->writeLog('statusesList');
        }
        return $statusCodeList;
    }

    /**
     * Запись в лог-файл ошибки API запроса
     *
     * @param $method API метод
     */
    private function writeLog($method)
    {
        file_put_contents(__DIR__ . '/error.log', json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $this->response->getStatusCode(),
            'msg' => $this->response->getErrorMsg()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }

    public function printTable()
    {
        return $this->table;
    }
}