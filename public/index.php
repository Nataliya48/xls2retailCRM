<?php
require_once '../vendor/autoload.php';
require '../src/LoadFile.php';
require '../src/SendRequest.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dotenv = Dotenv\Dotenv::create(realpath(__DIR__ . '/../'));
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $orders = new LoadFile($_FILES['file']);
        var_dump($orders);
        $query = new SendRequest($urlCrm, $apiKey, $orders);
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
require '../src/template.php';