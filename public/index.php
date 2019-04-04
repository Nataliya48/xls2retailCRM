<?php
require_once '../vendor/autoload.php';
require '../src/LoadFile.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');
*/
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //var_dump(['POST'=>$_POST]);
        //var_dump(['SERVER'=>$_SERVER]);
        $orders = new LoadFile($_FILES['file']);
        print_r(pathinfo ($_FILES['file']['name'], PATHINFO_EXTENSION));
        //print_r(['1'=>'1','Extension' => $orders->getExtension($_FILES['file']['name'])]);
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
var_dump($_FILES);
require '../src/template.php';