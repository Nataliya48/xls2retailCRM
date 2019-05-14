<?php

namespace Export;

Class SendRequest
{
    /**
     * @var \RetailCrm\Response\ApiResponse
     */
    //private $response;

    /**
     * @var array
     */
    private $table;
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



        //'car' => 'fast'
        //array_search("car",array_keys($a)); = 1
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

    //формировать каждую строку как отдельный заказ перед отправкой
    //лучше использовать upload и формировать до 50 заказов для этого запроса
    //взять таблицу, взять отдельный элемент в строке, получить его индекс
    //получить индекс поля из файла, к которому относится это поле (в какой колонке поле, в такой и название)
    //получить символьный код поля из CRM под этим индексом после выставления соответствия
    //при формировании массива для отправки выставить [поле срм]=>[значение из таблицы]
}