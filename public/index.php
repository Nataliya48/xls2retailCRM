<?php
session_start();
require_once '../vendor/autoload.php';

use Query\Select;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($_GET['action']) {
            case "select" :
                $_SESSION['type'] = $_POST['type'];
                require_once('../src/template/createOrder.php');
                break;
            case "send" :
                $responce = new Select($_POST['url'], $_POST['apiKey'], $_POST['order'], $_SESSION['type'], $_POST['site']);
                require_once('../src/template/result.php');
                break;
            case "start" :
                require_once('../src/template/start.php');
                $_SESSION['type'] = $_POST['type'];
                break;
        }
    } else {
        require_once('../src/template/start.php');
        //$_SESSION['type'] = $_POST['type'];
    }
} catch (Exception $e) {
    $errorMsg = 'Выброшено исключение: ' . $e->getMessage() . "\n";
}

