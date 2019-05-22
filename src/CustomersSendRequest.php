<?php

namespace Export;

class CustomersSendRequest
{
    /**
     * Массив клиентов
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
     * @param $table массив клиентов
     * @param $fieldsCrm поля из CRM
     * @param $fieldsFile поля из загруженного файла
     * @param $type тип загружаемых данных
     * @param $site сайт CRM
     */
    public function __construct($url, $apiKey, $table, $fieldsCrm, $fieldsFile, $site)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        unset($table[0]);
        $this->table = $table;
        $this->fieldsCrm = $fieldsCrm;
        $this->fieldsFile = $fieldsFile;
        $this->site = $site;
        $this->essenceCrm = [];

        $portions = array_chunk($this->assemblyCustomer(), 50, true);
        foreach ($portions as $portion) {
            $this->createCustomers($portion);
        }
    }

    /**
     * Формирование массива для отправки
     *
     * @return array
     */
    public function assemblyCustomer()
    {
        $assemblyCustomerCrm = [];
        foreach ($this->table as $customer){
            unset($this->essenceCrm);
            unset($this->payment);
            $assemblyCustomerCrm[] = $this->addValuesToFields($customer);
        }
        return $assemblyCustomerCrm;
    }

    /**
     * Добавление значений в массив клиента
     *
     * @param $customer клиент
     * @return array
     */
    private function addValuesToFields($customer)
    {
        foreach ($customer as $keyFieldFile => $fieldFile){
            if ($fieldFile === null){
                continue;
            }
            $keyFieldCrm = array_search($keyFieldFile, array_keys($this->fieldsCrm));
            if (strpos($this->fieldsCrm[$keyFieldCrm], '.')){
                $fieldExplode = explode('.', $this->fieldsCrm[$keyFieldCrm]);
                switch ($fieldExplode[0]) {
                    case "phones" :
                        $this->addPhonesToCustomer($fieldExplode, $fieldFile);
                        break;
                    case "contragent" :
                        $this->addContragentToCustomer($fieldExplode, $fieldFile);
                        break;
                    default:
                        $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
                        break;
                }
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'null'){
                continue;
            } else {
                $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
            }
        }
        return $this->essenceCrm;
    }

    /**
     * Добавление телефона в массив клиента
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return array
     */
    private function addPhonesToCustomer($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'number'){
            return $this->essenceCrm[$fieldExplode[0]][] = ['number' => $fieldFile];
        }
    }

    /**
     * Добавление данных контрагентов в массив клиента
     *
     * @param $fieldExplode поля CRM
     * @param $fieldFile значение для записи
     * @return array
     */
    private function addContragentToCustomer($fieldExplode, $fieldFile)
    {
        return $this->essenceCrm[$fieldExplode[0]] = [$fieldExplode[1] => $fieldFile];
    }

    /**
     * Массовое создание пакета заказов
     *
     * @param $portion массив заказов
     */
    private function createCustomers($portion)
    {
        try {
            $this->responce = $this->connectionToCrm()->request->customersUpload($portion, $this->site);
            $this->writeLogAssemblyCustomer('customersUpload', $this->responce, $portion);
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        if (!$this->responce->isSuccessful()) {
            $this->writeLogError('customersUpload', $this->responce);
        }
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
     * @param $customer массив клиента
     */
    private function writeLogAssemblyCustomer($method, $response, $customer)
    {
        file_put_contents(realpath(__DIR__ . '/../logs/assemblyCustomer.log'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $response->getStatusCode(),
            'orders' => $customer
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }
}