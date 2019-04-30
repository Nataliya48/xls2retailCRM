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

<form method="post" class="form">
    <input type="hidden" name="action" value="connect">
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
    <br>
    <p><input type="submit" value="Отправить"></p>
</form>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya</p>
</footer>

</body>