<?php
require_once '../vendor/autoload.php';
include '../src/LoadFile.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');
*/

try {
    $orders = new LoadFile($_FILES['file']['tmp_name']);
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
var_dump($_FILES);
include '../src/template.php';