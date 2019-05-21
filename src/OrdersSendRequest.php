<?php

namespace Export;

use Carbon\Carbon;

class OrdersSendRequest
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
     * Запрос для отправки заказов в CRM
     *
     * @var array
     */
    private $responce;

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
     * @param $url адрес CRM
     * @param $apiKey API ключ
     * @param $table массив заказов
     * @param $fieldsCrm поля из CRM
     * @param $fieldsFile поля из загруженного файла
     * @param $type тип загружаемых данных
     * @param $site сайт CRM
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
                switch ($fieldExplode[0]) {
                    case "items" :
                        $this->addItemsToOrder($fieldExplode, $fieldFile);
                        break;
                    case "payments" :
                        $this->addPaymentToOrder($fieldExplode, $fieldFile);
                        break;
                    case "delivery" :
                        $this->addDeliveryToOrder($fieldExplode, $fieldFile);
                        break;
                    case "customer" :
                        $this->addCustomerToOrder($fieldExplode, $fieldFile);
                        break;
                    default:
                        $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
                        break;
                }
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'status') {
                $this->addStatusToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'createdAt') {
                //$this->addDateCreatedToOrder($keyFieldCrm, $fieldFile);
                $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
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
            return $this->essenceCrm[$fieldExplode[0]][] = ['offer' => [$fieldExplode[1] => $fieldFile]];
        } elseif ($fieldExplode[1] === 'productName') {
            return $this->essenceCrm[$fieldExplode[0]][] = [$fieldExplode[1] => $fieldFile];
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
                    return $this->payment[$fieldExplode[1]] = $code;
                }
            }
        }
        if ($fieldExplode[1] === 'status') {
            foreach ($this->getListPaymentStatus() as $code => $status){
                if ($status === $fieldFile and $code != null) {
                    return $this->payment[$fieldExplode[1]] = $code;
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
     * Добавление даты создания заказа в массив заказа
     *
     * @param $keyFieldCrm ключ поля CRM
     * @param $fieldFile значение для записи
     * @return bool|\DateTime
     */
    private function addDateCreatedToOrder($keyFieldCrm, $fieldFile)
    {
        if (preg_match("/\d{2}\.\d{2}\.\d{4}\s\d{2}:\d{2}:\d{2}/", $fieldFile)){ //01.02.2018 00:00:00
            $date = Carbon::createFromFormat('d.m.Y H:i:s', $fieldFile);
        } elseif (preg_match("/^\d{2}\.\d{2}\.\d{4}$/", $fieldFile)){ //03.07.2018
            $date = Carbon::createFromFormat('d.m.Y', $fieldFile);
        } elseif (preg_match("/^\d{2}\.\d{2}\.\d{2}\s\d{2}:\d{2}$/", $fieldFile)){ //06.01.18 00:00
            $date = Carbon::createFromFormat('d.m.y H:i', $fieldFile);
        } elseif (preg_match("/^\d{4}-\d{2}-\d{2}$/", $fieldFile)){ //2018-03-11
            $date = Carbon::createFromFormat('Y-m-d', $fieldFile);
        } elseif (preg_match("/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/", $fieldFile)){ //2018-03-11 00:00:00
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $fieldFile);
        } else {
            return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
        }
        $date->format('Y-m-d H:i:s');
        return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $date['date'];
    }

    /**
     * Массовое создание пакета заказов
     *
     * @param $portion массив заказов
     */
    private function createOrders($portion)
    {
        try {
            $this->responce = $this->connectionToCrm()->request->ordersUpload($portion, $this->site);
            $this->writeLogAssemblyOrder('ordersUpload', $this->responce, $portion);
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        if (!$this->responce->isSuccessful()) {
            $this->writeLogError('ordersUpload', $this->responce);
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

    /**
     * @param $response
     * @return mixed
     */
    public function errorMassage()
    {
        return !empty($this->responce['errors']) ? $this->responce['errors'] : null;
    }

    /**
     * Запись в лог-файл сформированный массив API запроса
     *
     * @param $method API метод
     * @param $response запрос
     * @param $order массив заказа
     */
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