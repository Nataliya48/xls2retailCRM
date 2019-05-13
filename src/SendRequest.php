<?php

namespace Export;

Class SendRequest
{
    /**
     * @var \RetailCrm\Response\ApiResponse
     */
    private $response;

    /**
     * @var array
     */
    private $table;

    /**
     * Подключение к CRM
     *
     * @param $urlCrm адрес CRM
     * @param $apiKey ключ API
     * @return \RetailCrm\ApiClient
     */
    private function connectionToCrm($urlCrm, $apiKey)
    {
        $client = new \RetailCrm\ApiClient(
            $urlCrm,
            $apiKey,
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
    public function __construct($urlCrm, $apiKey, $table, $fieldsCrm, $fieldsFile)
    {
        unset($table[0]);
        $this->table = $table;

        try {
            $this->response = $this->connectionToCrm($urlCrm, $apiKey)->request->ordersList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }

        $this->getMapp($fieldsCrm, $fieldsFile);
        //'car' => 'fast'
        //array_search("car",array_keys($a)); = 1
    }

    private function getMapp($fieldsCrm, $fieldsFile)
    {

    }

    //формировать каждую строку как отдельный заказ перед отправкой
    //лучше использовать upload и формировать до 50 заказов для этого запроса
    //взять таблицу, взять отдельный элемент в строке, получить его индекс
    //получить индекс поля из файла, к которому относится это поле (в какой колонке поле, в такой и название)
    //получить символьный код поля из CRM под этим индексом после выставления соответствия
    //при формировании массива для отправки выставить [поле срм]=>[значение из таблицы]
}