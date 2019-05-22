<?php session_start();?>
<?php $errorMassage = $_SESSION['errorMassage'];?>

<html>
<head>
    <link href="style.css" type="text/css" rel="stylesheet"/>
    <title>Import2CRM</title>
</head>
<body>

<header class="header">
<h1 class="header-title">Import2CRM</h1>
<p class="text">Импорт заказов и клиентов из файлов в формате Microsoft Excel либо CSV в retailCRM</p>
</header>

<p class="head"></p>

<div class="err">
    <?php if ($errorMassage !== null): ?>
        <p class="load">Во время загрузки возникли следующие ошибки (скачать лог ошибок).</p>

        <?php
            $path = __DIR__ . '/../logs/' . $errorMassage;
            if (file_exists($path)) {
                header('X-Accel-Redirect: ' . $path);
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($path));
                exit;
            }
        ?>

    <?php else: ?>
        <p class="load">Загрузка успешно завершена.</p>
    <?php endif; ?>
</div>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya <a href="templateDocumentation.php">Документация</a></p>
</footer>

</body>