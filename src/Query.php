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
                    $type,
                    $site
                );
                $_SESSION['errorMassage'] = $request->errorMassage();
                break;
            case 'customers':
                break;
        }

    }

}