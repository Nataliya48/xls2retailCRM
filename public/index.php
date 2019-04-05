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
        $table = new LoadFile($_FILES['file']);
        $data = $table->getFileContents();
        //var_dump($data);
        $query = new SendRequest($urlCrm, $apiKey, $data);
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
require '../src/template.php';