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

<div class="first">
    <form enctype="multipart/form-data" method="post" class="form" action="index.php?action=load">
        <p class="load">Загрузите файл в формате *.xls(x) или *.csv</p>
        <p class="load">Файл в формате CSV должен иметь кодировку utf8 с разделителем колонок ";" (точка с запятой).</p>
        <p class="load">Также укажите данные для подключения к Вашей retailCRM.</p>
        <input type="hidden" name="action" value="load">
        <p><input type="file" name="file" multiple
                  accept="text/plain,application/excel,application/vnd.ms-excel,application/x-excel,application/x-msexcel"></p>
        <table border="0">
            <tr><td>
                    Введите адрес retailCRM:
                </td>
                <td>
                    <input type="text" required name="url" placeholder="https://demo.retailcrm.ru">
                </td>
            </tr>
            <tr><td>
                    Введите API ключ:
                </td>
                <td>
                    <input type="text" required name="apiKey" placeholder="RiycYM83RnTR4dS7AKasJr0jtKpMe6j7">
                </td>
            </tr>
        </table>
        <p><input type="submit" value="Отправить"></p>
    </form>
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