<?php
require_once '../vendor/autoload.php';
require '../src/LoadFile.php';
require '../src/SendRequest.php';
require '../src/ConnectCrm.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*$dotenv = Dotenv\Dotenv::create(realpath(__DIR__ . '/../'));
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');*/

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //$file = new LoadFile($_FILES['file']);
        //$table = $file->getFileContents();
        //$fields = $file->getNamesFields();
        $connect = new ConnectCrm($_POST['url'], $_POST['apiKey']);
        $sites = $connect->getSiteName();
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
require '../src/template.php';