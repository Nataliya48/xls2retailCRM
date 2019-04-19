<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export;

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