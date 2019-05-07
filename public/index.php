<?php
session_start();
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export\LoadFile;
use Export\ConnectCrm;
use Export\SendRequest;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($_GET['action']) {
            case "load" :
                $file = new LoadFile($_FILES['file']);
                $table = $file->getFileContents();
                $fieldsFile = $file->getNamesFields();
                $connect = new ConnectCrm($_POST['url'], $_POST['apiKey']);
                $sites = $connect->getSiteName();
                $listFields = $connect->listFields()['orders'];
                $_SESSION['listFields'] = $listFields;
                require_once("../src/templateSelectOption.php");
                break;
            case "connect" :
                require_once("../src/templateMapping.php");
                break;
            default :
                require_once("../src/template404.php"); //сделать 404 страницу
                break;
        }
    } else {
        require_once('../src/templateLoadFile.php');
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
