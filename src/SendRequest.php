<?php

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
     * @var array
     */
    private $symbolicCode;

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
    public function __construct($urlCrm, $apiKey, $table, $mapping)
    {
        $this->symbolicCode = $table[0];
        unset($table[0]);
        $this->table = $table;

        try {
            $this->response = $this->connectionToCrm($urlCrm, $apiKey)->request->ordersList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
        //на форме выбирается тип данных, которые загружаются в систему (заказы, клиенты, статусы или тд)
        //в зависимости от того, какой тип выбран, вызывается метод
    }

    // каждой колонке соответствует свой символьный код поля срм
    // каждая ячейка загружается в то поле, которое выставлено в соответствии

    private function getMapp()
    {
        //будет хранить массив [ключ]=>[значение]
        //[Название поля таблицы]=>[Код поля CRM]
    }
}