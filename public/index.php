<?php
require_once '../vendor/autoload.php';
require '../src/LoadFile.php';
require '../src/SendRequest.php';
require '../src/MatchSetting.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dotenv = Dotenv\Dotenv::create(realpath(__DIR__ . '/../'));
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $file = new LoadFile($_FILES['file']);
        $table = $file->getFileContents();
        $settings = new MatchSetting($table);
        $fields = $settings->getNamesFields();
        //$query = new SendRequest($urlCrm, $apiKey, $table);
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
require '../src/template.php';