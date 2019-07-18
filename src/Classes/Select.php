<?php


namespace Query;


class Select
{
    public function __construct($url, $apiKey, $table, $type, $site)
    {
        switch ($type){
            case 'orders':
                $request = new CreateOrder(
                    $url,
                    $apiKey,
                    $table,
                    $site
                );
                $_SESSION['massage'] = $request->ordersCreate();
                break;
        }
    }
}