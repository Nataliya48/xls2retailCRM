<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export\LoadFile;
use Export\ConnectCrm;
use Export\SendRequest;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        switch ($_POST['action']) {
            case "load" :
                require_once("../src/templateConnectCrm.php");
                $connect = new ConnectCrm($_POST['url'], $_POST['apiKey']);
                $sites = $connect->getSiteName();
                $listFields = $connect->listFields()['orders'];
                break;
            case "connect" :
                require_once("../src/templateMapping.php");
                break;
            case "mapping" :
                require_once("");
                break;
            default :
                require_once("page404.php"); //сделать 404 страницу
                break;
        }

        $request = new SendRequest($_POST['url'], $_POST['apiKey'], $table, $mapping);

    } else {
        require_once('../src/templateLoadFile.php');
        $file = new LoadFile($_FILES['file']);
        $table = $file->getFileContents();
        $fieldsFile = $file->getNamesFields();
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
