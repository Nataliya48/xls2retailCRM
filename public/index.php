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
                $_SESSION['table'] = $table;
                $fieldsFileLoad = $file->getNamesFields();
                $_SESSION['url'] = $_POST['url'];
                $_SESSION['apiKey'] = $_POST['apiKey'];
                $connect = new ConnectCrm($_SESSION['url'], $_SESSION['apiKey']);
                $sites = $connect->getSiteName();
                $listFieldsCrm = $connect->listFields();
                $customFields = $connect->customFields();
                $_SESSION['listFieldsCrm'] = $listFieldsCrm;
                $_SESSION['fieldsFileLoad'] = $fieldsFileLoad;
                $_SESSION['customFields'] = $customFields;
                require_once("../src/templateSelectOption.php");
                break;
            case "connect" :
                require_once("../src/templateMapping.php");
                $_SESSION['type'] = $_POST['type'];
                $_SESSION['site'] = $_POST['site'];
                break;
            case "mapping" :
                $request = new SendRequest(
                    $_SESSION['url'],
                    $_SESSION['apiKey'],
                    $_SESSION['table'],
                    $_POST['crm'],
                    $_POST['file'],
                    $_SESSION['type'],
                    $_SESSION['site']
                );
                $assemblyOrder = $request->assemblyOrder();
                $_SESSION['assemblyOrder'] = $assemblyOrder;
                require_once("../src/templateFinal.php");
                break;
            default :
                require_once("../src/template404.php");
                break;
        }
    } else {
        require_once('../src/templateLoadFile.php');
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
