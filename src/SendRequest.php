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
     * Массив оплаты
     *
     * @var array
     */
    private $payment;

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
            unset($this->essenceCrm);
            unset($this->payment);
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
            if ($fieldFile === null){
                continue;
            }
            $keyFieldCrm = array_search($keyFieldFile, array_keys($this->fieldsCrm));
            if (strpos($this->fieldsCrm[$keyFieldCrm], '.')){
                $fieldExplode = explode('.', $this->fieldsCrm[$keyFieldCrm]);
                if ($fieldExplode[0] === 'items') {
                    $this->addItemsToOrder($fieldExplode, $fieldFile);
                } elseif ($fieldExplode[0] === 'payments') {
                    $this->addPaymentToOrder($fieldExplode, $fieldFile);
                } elseif ($fieldExplode[0] === 'delivery') {
                    $this->addDeliveryToOrder($fieldExplode, $fieldFile);
                } elseif ($fieldExplode[0] === 'customer'){
                    $this->addCustomerToOrder($fieldExplode, $fieldFile);
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
            if ($this->payment !== null) {
                $this->essenceCrm['payments'] = [$this->payment];
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
            return $this->essenceCrm[$fieldExplode[0]][] = ['offer' => ['externalId' => $fieldFile]];
        } elseif ($fieldExplode[1] === 'name') {
            return $this->essenceCrm[$fieldExplode[0]][] = ['productName' => $fieldFile];
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
                if ($payment === $fieldFile and $code != null){
                    return $this->payment['type'] = $code;
                }
            }
        }
        if ($fieldExplode[1] === 'status') {
            foreach ($this->getListPaymentStatus() as $code => $status){
                if ($status === $fieldFile and $code != null) {
                    return $this->payment['status'] = $code;
                }
            }
        }
    }

    /**
     * Добавление доставки в массив заказа
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return int|string
     */
    private function addDeliveryToOrder($fieldExplode, $fieldFile)
    {
        foreach ($this->getListDeliveryCode() as $code => $delivery) {
            if ($delivery === $fieldFile) {
                return $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $code];
            }
        }
    }

    /**
     * Добавление клиента в массив заказа
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return array
     */
    private function addCustomerToOrder($fieldExplode, $fieldFile)
    {
        return $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
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
            $this->writeLogAssemblyOrder('ordersUpload', $response, $portion);
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        if (!$response->isSuccessful()) {
            $this->writeLogError('ordersUpload', $response);
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
            $this->writeLogError('statusesList', $response);
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
            $this->writeLogError('paymentTypesList', $response);
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
            $this->writeLogError('paymentStatusesList', $response);
        }
        return $paymentStatusCodeList;
    }

    /**
     * Получить список символьных кодов типов доставок из CRM
     *
     * @return array
     */
    private function getListDeliveryCode()
    {
        try {
            $response = $this->connectionToCrm()->request->deliveryTypesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $deliveryCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->deliveryTypes as $delivery){
                $deliveryCodeList[$delivery['code']] = $delivery['name'];
            }
        } else {
            $this->writeLogError('deliveryTypesList', $response);
        }
        return $deliveryCodeList;
    }

    /**
     * Запись в лог-файл ошибки API запроса
     *
     * @param $method API метод
     * @param $response запрос
     */
    private function writeLogError($method, $response)
    {
        file_put_contents(realpath(__DIR__ . '/../logs/error.log'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $response->getStatusCode(),
            'msg' => $response->getErrorMsg(),
            'error' => isset($response['errors']) ? $response['errors'] : 'not errors'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }

    private function writeLogAssemblyOrder($method, $response, $order)
    {
        file_put_contents(realpath(__DIR__ . '/../logs/assemblyOrder.log'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $response->getStatusCode(),
            'orders' => $order
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }
}