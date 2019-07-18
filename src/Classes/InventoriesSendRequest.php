<?php

namespace Export;

class InventoriesSendRequest
{
    /**
     * Массив остатков и закупочных цен
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
     * Поля из CRM
     *
     * @var array
     */
    private $fieldsCrm;

    /**
     * Поля из файла xls
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
     * Массив остатков CRM
     *
     * @var array
     */
    private $inventories;

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
     * @param $table array массив клиентов
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

        $portions = array_chunk($this->assemblyInventories(), 50, true);
        foreach ($portions as $portion) {
            $this->inventoriesUpload($portion);
        }
    }

    /**
     * Формирование массива для отправки
     *
     * @return array
     */
    public function assemblyInventories()
    {
        $assemblyInventories = [];
        foreach ($this->table as $inventories){
            unset($this->essenceCrm);
            unset($this->inventories);
            $assemblyInventories[] = $this->addValuesToFields($inventories);
        }
        return $assemblyInventories;
    }

    /**
     * Добавление значений в массив остатков
     *
     * @param $inventories array остатки
     * @return array
     */
    private function addValuesToFields($inventories)
    {
        foreach ($inventories as $keyFieldFile => $fieldFile){
            if ($fieldFile === null){
                continue;
            }
            $keyFieldCrm = array_search($keyFieldFile, array_keys($this->fieldsCrm));
            if (strpos($this->fieldsCrm[$keyFieldCrm], '.')) {
                $fieldExplode = explode('.', $this->fieldsCrm[$keyFieldCrm]);
                if ($fieldExplode[0] === 'stores') {
                    $this->addCodeStoreToInventories($fieldExplode, $fieldFile);
                }
            } elseif ($this->fieldsCrm[$keyFieldCrm] === 'null'){
                continue;
            } else {
                $this->essenceCrm[$this->fieldsCrm[$keyFieldCrm]] = $fieldFile;
            }
        }
        if (!empty($this->inventories) /*&& $this->inventories !== null*/) {
            $this->essenceCrm['stores'] = [$this->inventories];
        }
        return $this->essenceCrm;
    }

    /**
     * Добавление информации по складу в массив остатков
     *
     * @param $fieldExplode array поля CRM
     * @param $fieldFile string значение для записи
     */
    private function addCodeStoreToInventories($fieldExplode, $fieldFile)
    {
        if ($fieldExplode[1] === 'code'){
            $this->inventories[$fieldExplode[1]] = $fieldFile;
        } elseif ($fieldExplode[1] === 'available' && $this->inventories['code'] !== null) {
            $this->inventories[$fieldExplode[1]] = $fieldFile;
        } elseif ($fieldExplode[1] === 'purchasePrice' && $this->inventories['code'] !== null) {
            $this->inventories[$fieldExplode[1]] = $fieldFile;
        }
    }


    /**
     * Загрузка остатков
     *
     * @param $portion array массив остатков
     */
    private function inventoriesUpload($portion)
    {
        try {
            $this->responce = $this->connectionToCrm()->request->storeInventoriesUpload($portion, $this->site);
            $this->writeLogAssemblyCustomer('storeInventoriesUpload', $this->responce, $portion);
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
     * @param $customer array массив клиента
     */
    private function writeLogAssemblyCustomer($method, $response, $customer)
    {
        file_put_contents(realpath(__DIR__ . '/../logs/assemblyInventories.log'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'code' => $response->getStatusCode(),
            'orders' => $customer
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    }
}