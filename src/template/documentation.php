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

<h1>Импорт клиентов или заказов в систему</h1>
<p class="load">Перед загрузкой базы заказов или клиентов рекомендуем ознакомиться с инструкцией по использованию, а также проверить файл на корректность данных.</p>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya</p>
</footer>

</body>