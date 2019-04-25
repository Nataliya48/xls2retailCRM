<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export\LoadFile;
use Export\ConnectCrm;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $file = new LoadFile($_FILES['file']);
        $table = $file->getFileContents();
        $fieldsFile = $file->getNamesFields();
        $connect = new ConnectCrm($_POST['url'], $_POST['apiKey']);
        $sites = $connect->getSiteName();
        $listFields = $connect->listFields()['orders'];
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}

require '../src/templateLoadFile.php';