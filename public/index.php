<?php
session_start();
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export\LoadFile;
use Export\ConnectCrm;
use Export\Query;

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
                require_once("../src/template/templateSelectOption.php");
                break;
            case "connect" :
                $_SESSION['type'] = $_POST['type'];
                $_SESSION['site'] = $_POST['site'];
                require_once("../src/template/templateMapping.php");
                break;
            case "mapping" :
                $request = new Query(
                    $_SESSION['url'],
                    $_SESSION['apiKey'],
                    $_SESSION['table'],
                    $_POST['crm'],
                    $_POST['file'],
                    $_SESSION['type'],
                    $_SESSION['site']
                );
                require_once("../src/template/templateFinal.php");
                break;
            case "docs":
                require_once("../src/template/templateDocumentation.php");
                break;
            default :
                require_once("../src/template/template404.php");
                break;
        }
    } else {
        require_once('../src/template/templateLoadFile.php');
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
