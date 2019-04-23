<?php
require_once '../vendor/autoload.php';
//require '../src/ConnectCrm.php';
//require '../src/SendRequest.php';
//require '../src/LoadFile.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Export\PhpLoadFile\LoadFile;
use Export\ConnectCrm;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //$file = new LoadFile($_FILES['file']);
        //$table = $file->getFileContents();
        //$fields = $file->getNamesFields();
        $connect = new ConnectCrm($_POST['url'], $_POST['apiKey']);
        $sites = $connect->getSiteName();
        $listFields = $connect->listFields()->orders;
        /*echo '<pre>';
        foreach ($listFields as $fields){
            if (is_object($fields)){
                foreach ($fields as $field){
                    print_r($field );
                }
            } else {
                print_r($fields );
            }
        }
        echo '</pre>';*/
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}
require '../src/template.php';