<?php

namespace Export;

class SendRequest
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
     * Магазин из CRM
     *
     * @var string
     */
    private $site;

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
    public function __construct($url, $apiKey, $table, $fieldsCrm, $fieldsFile, $type, $site)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        unset($table[0]);
        $this->table = $table;
        $this->fieldsCrm = $fieldsCrm;
        $this->fieldsFile = $fieldsFile;
        $this->site = $site;

        if ($type === 'orders') {
            $portions = array_chunk($this->assemblyOrder(), 50, true);
            foreach ($portions as $portion) {
                $this->createOrders($portion);
            }
        }
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
                if (strpos($this->fieldsCrm[$keyFieldCrm], '.')){
                    $fields = explode('.', $this->fieldsCrm[$keyFieldCrm]);
                    if ($fields[0] === 'items' and $fields[1] === 'externalId'){
                        $orderCrm[$fields[0]] = [['offer' => ['externalId' => $field]]];
                    } elseif ($fields[0] === 'items' and $fields[1] === 'name') {
                        $orderCrm[$fields[0]] = [['name' => $field]];
                    } else {
                        $orderCrm[$fields[0]] = [$fields[1] => $field];
                    }
                } elseif ($this->fieldsCrm[$keyFieldCrm] === 'status'){
                    foreach ($this->getListStatusCode() as $code => $status){
                        if ($status === $field){
                            $orderCrm[$this->fieldsCrm[$keyFieldCrm]] = $code;
                        }
                    }
                } elseif ($this->fieldsCrm[$keyFieldCrm] === 'null'){
                    continue;
                } else {
                    $orderCrm[$this->fieldsCrm[$keyFieldCrm]] = $field;
                }
            }
            $assemblyOrderCrm[] = $orderCrm;
        }
        return $assemblyOrderCrm;
    }

    /**
     * Массовое создание пакета заказов
     *
     * @param $portion массив заказов
     */
    private function createOrders($portion)
    {
        try {
            $response = $this->connectionToCrm()->request->ordersUpload($portion, $this->site);
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        if (!$response->isSuccessful()) {
            $this->writeLog('ordersUpload', $response);
        }
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
            $this->writeLog('statusesList', $response);
        }
        return $statusCodeList;
    }

    /**
     * Запись в лог-файл ошибки API запроса
     *
     * @param $method API метод
     * @param $response запрос
     */
    private function writeLog($method, $response)
    {
        file_put_contents(realpath(__DIR__ . '/../logs/error.log'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $response->getStatusCode(),
            'msg' => $response->getErrorMsg()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }

    public function printTable()
    {
        return $this->table;
    }
}