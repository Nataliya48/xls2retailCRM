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

<div>
    <form enctype="multipart/form-data" method="post" class="form">
        <p class="load">Загрузите файл в формате *.xls(x) или *.csv</p>
        <p class="load">Файл в формате CSV должен иметь кодировку utf8 с разделителем колонок ";" (точка с запятой).</p>
        <input type="hidden" name="action" value="load">
        <p><input type="file" name="file" multiple
                  accept="text/plain,application/excel,application/vnd.ms-excel,application/x-excel,application/x-msexcel">
            <input type="submit" value="Загрузить файл"></p>
    </form>
</div>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya</p>
</footer>

</body>