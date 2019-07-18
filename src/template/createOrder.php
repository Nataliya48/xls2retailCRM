<html>
<body>

<form method="post" action="index.php?action=send">
    <p class="load">Заполните поля для создания заказа</p>
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
        <tr><td>
                Введите site:
            </td>
            <td>
                <input type="text" required name="site" placeholder="www-demo-ru">
            </td>
        </tr>
        <tr><td>
                Введите массив в json формате:
            </td>
            <td>
                <input type="text" required name="order" placeholder='{"status":"new","number":"N100"}'>
            </td>
        </tr>
    </table>
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