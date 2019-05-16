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
     * Отдельный массив значений для отправки в CRM
     *
     * @var array
     */
    private $essenceCrm;

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
        $this->essenceCrm = [];

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
            $assemblyOrderCrm[] = $this->addValuesToFields($order);
        }
        return $assemblyOrderCrm;
    }

    /**
     * Добавление значений в массив заказа
     *
     * @param $order заказ
     * @return array
     */
    private function addValuesToFields($order)
    {
        foreach ($order as $keyFieldFile => $fieldFile){
            $keyFieldCrm = array_search($keyFieldFile, array_keys($this->fieldsCrm));
            if (strpos($this->fieldsCrm[$keyFieldCrm], '.')){
                $fieldExplode = explode('.', $this->fieldsCrm[$keyFieldCrm]);
                if ($fieldExplode[0] === 'items') {
                    $this->addItemsToOrder($fieldExplode, $fieldFile);
                } elseif ($fieldExplode[0] === 'payments') {
                    $this->addPaymentToOrder($fieldExplode, $fieldFile);
                } else {
                    $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
                }
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'status') {
                $this->addStatusToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'null'){
                continue;
            } else {
                $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
            }
        }
        return $this->essenceCrm;
    }

    /**
     * Добавление товара в массив заказа
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return array
     */
    private function addItemsToOrder($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'externalId'){
            return $this->essenceCrm[$fieldExplode[0]] = [['offer' => ['externalId' => $fieldFile]]];
        } elseif ($fieldExplode[1] === 'name') {
            return $this->essenceCrm[$fieldExplode[0]] = [['name' => $fieldFile]];
        }
    }

    /**
     * Добавление оплаты в массив заказа
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return array
     */
    private function addPaymentToOrder($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'type'){
            foreach ($this->getListPaymentCode() as $code => $payment){
                if ($payment === $fieldFile){
                    return $this->essenceCrm[$fieldExplode[0]] = [['type' => $code]];
                }
            }
        } elseif ($fieldExplode[1] === 'status') {
            foreach ($this->getListPaymentStatus() as $code => $status){
                if ($status === $fieldFile) {
                    return $this->essenceCrm[$fieldExplode[0]] = [['status' => $code]];
                }
            }
        }
    }

    /**
     * Добавление статуса в массив заказа
     *
     * @param $keyFieldCrm ключ поля CRM
     * @param $fieldFile значение для записи
     * @return mixed
     */
    private function addStatusToOrder($keyFieldCrm, $fieldFile)
    {
        foreach ($this->getListStatusCode() as $code => $status){
            if ($status === $fieldFile){
                return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $code;
            }
        }
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
     * Получить список символьных кодов типов оплат из CRM
     *
     * @return array
     */
    private function getListPaymentCode()
    {
        try {
            $response = $this->connectionToCrm()->request->paymentTypesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $paymentCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->paymentTypes as $payment){
                $paymentCodeList[$payment['code']] = $payment['name'];
            }
        } else {
            $this->writeLog('paymentTypesList', $response);
        }
        return $paymentCodeList;
    }

    /**
     * Получить список символьных кодов статусов типов оплат из CRM
     *
     * @return array
     */
    private function getListPaymentStatus()
    {
        try {
            $response = $this->connectionToCrm()->request->paymentStatusesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $paymentStatusCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->paymentStatuses as $status){
                $paymentStatusCodeList[$status['code']] = $status['name'];
            }
        } else {
            $this->writeLog('paymentStatusesList', $response);
        }
        return $paymentStatusCodeList;
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
            'msg' => $response->getErrorMsg(),
            'error' => isset($response['errorMsg']) ? $response['errorMsg'] : 'not errors'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }
}