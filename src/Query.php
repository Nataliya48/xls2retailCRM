<?php

namespace Export;

use Export\OrdersSendRequest;
use Export\CustomersSendRequest;

class Query
{
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