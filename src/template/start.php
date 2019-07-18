<?php //session_start();?>
<html>
<body>

<form method="post" action="index.php?action=select">
    <p class="load">Какие действия необходимо произвести?</p>
    <table border="0" width="100%" cellpadding="5">
        <tr>
            <td width="50%">
                <select size="1" name="type">
                    <option selected disabled>Выберете действие</option>
                    <option value="orders">Создать заказ</option>
                    <option value="customers">Создать клиента</option>
                    <option value="inventories">Загрузить остатки</option>
                </select>
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