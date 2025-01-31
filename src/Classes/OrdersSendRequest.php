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
     * Массив товаров
     *
     * @var array
     */
    private $items;

    /**
     * Запрос для отправки заказов в CRM
     *
     * @var array
     */
    private $responce;

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
     * SendRequest constructor.
     * @param $url string адрес CRM
     * @param $apiKey string API ключ
     * @param $table array массив заказов
     * @param $fieldsCrm array поля из CRM
     * @param $fieldsFile array поля из загруженного файла
     * @param $site string сайт CRM
     */
    public function __construct($url, $apiKey, $table, $fieldsCrm, $fieldsFile, $site)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        $this->table = $table;
        $this->fieldsCrm = $fieldsCrm;
        $this->fieldsFile = $fieldsFile;
        $this->site = $site;
        $this->essenceCrm = [];

        $portions = array_chunk($this->assemblyOrder(), 50, true);
        foreach ($portions as $portion) {
            $this->createOrders($portion);
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
            unset($this->items);
            $assemblyOrderCrm[] = $this->addValuesToFields($order);
        }
        return $assemblyOrderCrm;
    }

    /**
     * Добавление значений в массив заказа
     *
     * @param $order array заказ
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
                        $this->addDeliveryTypeToOrder($fieldExplode, $fieldFile);
                        break;
                    case "address":
                        $this->addAddressDeliveryToOrder($fieldExplode, $fieldFile);
                        break;
                    default:
                        $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
                        break;
                }
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'status') {
                $this->addStatusToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'createdAt') {
                $this->addDateCreatedToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'orderType') {
                $this->addOrderTypeToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'orderMethod') {
                $this->addOrderMethodToOrder($keyFieldCrm, $fieldFile);
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'null'){
                continue;
            } else {
                $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
            }
            if (isset($this->payment['type']) && $this->payment['type'] !== null) {
                $this->essenceCrm['payments'] = [$this->payment];
            } if (isset($this->items['productName']) && $this->items['productName'] !== null) {
                $this->essenceCrm['items'] = [$this->items];
            }
        }
        return $this->essenceCrm;
    }

    /**
     * Добавление товара в массив заказа
     *
     * @param $fieldExplode array поля CRM
     * @param $fieldFile string значение для записи
     * @return array
     */
    private function addItemsToOrder($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'externalId'){
            return $this->items[$fieldExplode[0]][] = ['offer' => [$fieldExplode[1] => $fieldFile]];
        } elseif ($fieldExplode[1] === 'productName') {
            return $this->items[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'initialPrice' && $this->items['productName'] !== null) {
            return $this->items[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'quantity' && $this->items['productName'] !== null) {
            return $this->items[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'discountTotal' && $this->items['productName'] !== null) {
            return $this->items[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
        }

        /*if ($fieldExplode[1] === 'externalId'){
            return $this->essenceCrm[$fieldExplode[0]][] = ['offer' => [$fieldExplode[1] => $fieldFile]];
        } elseif ($fieldExplode[1] === 'productName') {
            return $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'initialPrice') {
            return $this->essenceCrm[$fieldExplode[0]][] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'quantity') {
            return $this->essenceCrm[$fieldExplode[0]][] = [$fieldExplode[1] => $fieldFile];
        } elseif ($fieldExplode[1] === 'discountTotal') {
            return $this->essenceCrm[$fieldExplode[0]][] = [$fieldExplode[1] => $fieldFile];
        }*/
    }

    /**
     * Добавление оплаты в массив заказа
     *
     * @param $fieldExplode array поля CRM
     * @param $fieldFile string значение для записи
     * @return array
     */
    private function addPaymentToOrder($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'type'){
            foreach ($this->getListPaymentCode() as $code => $payment){
                if ($payment === $fieldFile || $code === $fieldFile){
                    return $this->payment[$fieldExplode[1]] = $code;
                }
            }
        }
        if ($fieldExplode[1] === 'status' && $this->payment['type'] !== null) {
            foreach ($this->getListPaymentStatus() as $code => $status){
                if ($status === $fieldFile || $code === $fieldFile) {
                    return $this->payment[$fieldExplode[1]] = $code;
                }
            }
        }
    }

    /**
     * Добавление доставки в массив заказа
     *
     * @param $fieldExplode array поля CRM
     * @param $fieldFile string значение для записи
     * @return int|string
     */
    private function addDeliveryTypeToOrder($fieldExplode, $fieldFile)
    {
        foreach ($this->getListDeliveryCode() as $code => $delivery) {
            if ($delivery === $fieldFile || $code === $fieldFile) {
                return $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $code];
            }
        }
    }

    /**
     * Добавление статуса в массив заказа
     *
     * @param $keyFieldCrm string ключ поля CRM
     * @param $fieldFile string значение для записи
     * @return mixed
     */
    private function addStatusToOrder($keyFieldCrm, $fieldFile)
    {
        foreach ($this->getListStatusCode() as $code => $status){
            if ($status === $fieldFile || $code === $fieldFile){
                return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $code;
            }
        }
    }

    /**
     * Добавление адреса доставки в массив заказа
     *
     * @param $fieldExplode array поля CRM
     * @param $fieldFile string значение для записи
     * @return array
     */
    private function addAddressDeliveryToOrder($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'text'){
            foreach ($this->getListPaymentCode() as $code => $payment){
                if (($payment === $fieldFile || $code === $fieldFile) and $code != null){
                    return $this->payment['delivery'] = [$fieldExplode[1] => $code];
                }
            }
        }
    }

    /**
     * Добавление типа заказа в массив заказа
     *
     * @param $keyFieldCrm string ключ поля CRM
     * @param $fieldFile string значение для записи
     * @return mixed
     */
    private function addOrderTypeToOrder($keyFieldCrm, $fieldFile)
    {
        foreach ($this->getListOrderTypes() as $code => $type){
            if ($type === $fieldFile || $code === $fieldFile){
                return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $code;
            }
        }
    }

    /**
     * Добавление способа оформления заказа в массив заказа
     *
     * @param $keyFieldCrm string ключ поля CRM
     * @param $fieldFile string значение для записи
     * @return mixed
     */
    private function addOrderMethodToOrder($keyFieldCrm, $fieldFile)
    {
        foreach ($this->getListOrderMethods() as $code => $method){
            if ($method === $fieldFile || $code === $fieldFile){
                return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $code;
            }
        }
    }

    /**
     * Добавление даты создания заказа в массив заказа
     *
     * @param $keyFieldCrm string|\DateTime ключ поля CRM
     * @param $fieldFile string значение для записи
     * @return bool|\DateTime
     */
    private function addDateCreatedToOrder($keyFieldCrm, $fieldFile)
    {
        if (preg_match("/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\.\d{6}$/", (string)$fieldFile->date)) {
            $fieldFile = explode('.', $fieldFile)[0];
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $fieldFile);
        } elseif (preg_match("/\d{2}\.\d{2}\.\d{4}\s\d{2}:\d{2}:\d{2}/", $fieldFile)){ //01.02.2018 00:00:00
            $date = Carbon::createFromFormat('d.m.Y H:i:s', $fieldFile);
        } elseif (preg_match("/^\d{2}\.\d{2}\.\d{4}$/", $fieldFile)){ //03.07.2018
            $date = Carbon::createFromFormat('d.m.Y', $fieldFile);
        } elseif (preg_match("/^\d{2}\.\d{2}\.\d{2}\s\d{2}:\d{2}$/", $fieldFile)){ //06.01.18 00:00
            $date = Carbon::createFromFormat('d.m.y H:i', $fieldFile);
        } elseif (preg_match("/^\d{4}-\d{2}-\d{2}$/", $fieldFile)){ //2018-03-11
            $date = Carbon::createFromFormat('Y-m-d', $fieldFile);
        } elseif (preg_match("/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/", $fieldFile)) { //2018-03-11 00:00:00
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $fieldFile);
        } else {
            return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
        }
        $data = $date->format('Y-m-d H:i:s');
        return $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $data;
    }

    /**
     * Массовое создание пакета заказов
     *
     * @param $portion array массив заказов
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
     * Получить список символьных кодов методов оформления заказов из CRM
     *
     * @return array
     */
    private function getListOrderMethods()
    {
        try {
            $response = $this->connectionToCrm()->request->orderMethodsList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $statusCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->orderMethods as $method){
                $statusCodeList[$method['code']] = $method['name'];
            }
        } else {
            $this->writeLogError('orderMethodsList', $response);
        }
        return $statusCodeList;
    }

    /**
     * Получить список символьных кодов типов заказов из CRM
     *
     * @return array
     */
    private function getListOrderTypes()
    {
        try {
            $response = $this->connectionToCrm()->request->orderTypesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        $statusCodeList = [];
        if ($response->isSuccessful()) {
            foreach ($response->orderTypes as $type){
                $statusCodeList[$type['code']] = $type['name'];
            }
        } else {
            $this->writeLogError('orderTypesList', $response);
        }
        return $statusCodeList;
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
     * @param $method string API метод
     * @param $response \RetailCrm\ApiClient запрос
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
     * Список ошибок при загрузке для печати
     *
     * @return mixed
     */
    public function errorMsgForPrint()
    {
        return !empty($this->responce['errors']) ? $this->responce['errors'] : null;
    }

    /**
     * Запись в лог-файл сформированный массив API запроса
     *
     * @param $method string API метод
     * @param $response \RetailCrm\ApiClient запрос
     * @param $order array массив заказа
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