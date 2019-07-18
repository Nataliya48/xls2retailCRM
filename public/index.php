<?php
session_start();
require_once '../vendor/autoload.php';

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
                require_once("../src/template/selectOption.php");
                break;
            case "connect" :
                $_SESSION['type'] = $_POST['type'];
                $_SESSION['site'] = $_POST['site'];
                require_once("../src/template/mapping.php");
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
                require_once("../src/template/result.php");
                break;
            case "docs":
                require_once("../src/template/documentation.php");
                break;
            case "start":
                require_once('../src/template/loadFile.php');
                break;
            default :
                require_once("../src/template/notFound.php");
                break;
        }
    } else {
        require_once('../src/template/loadFile.php');
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
    file_put_contents(realpath(__DIR__ . '/../logs/throws.log'), json_encode([
        'date' => date('Y-m-d H:i:s'),
        'exception' => $errorMsg
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), FILE_APPEND);
    //require_once();
    //сделать шаблон для вывода ошибки
    //следать лог для записи исключений
}
