<?php

namespace Export;

class Query
{
    /**
     * Query constructor.
     * @param $url string адрес CRM
     * @param $apiKey string API ключ
     * @param $table array массив данных из загруженного файла
     * @param $fieldsCrm array поля из CRM
     * @param $fieldsFile array поля из загруженного файла
     * @param $type string тип загружаемых данных
     * @param $site string сайт CRM
     */
    public function __construct($url, $apiKey, $table, $fieldsCrm, $fieldsFile, $type, $site)
    {
        unset($table[0]);

        switch ($type){
            case 'orders':
                $request = new OrdersSendRequest(
                    $url,
                    $apiKey,
                    $table,
                    $fieldsCrm,
                    $fieldsFile,
                    $site
                );
                $_SESSION['errorMassage'] = $request->errorMsgForPrint();
                break;
            case 'customers':
                $request = new CustomersSendRequest(
                    $url,
                    $apiKey,
                    $table,
                    $fieldsCrm,
                    $fieldsFile,
                    $site
                );
                $_SESSION['errorMassage'] = $request->errorMsgForPrint();
                break;
        }
    }
}