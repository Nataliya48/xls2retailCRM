<?php
$massage = $_SESSION['massage'];
?>
<html>
<body>

<form method="post" class="formMapp" action="index.php?action=start">
    <p class="load">Результат:</p>
    <p><?php print_r($massage); ?></p>
    <p>Выполнить другую операцию?</p>
    <p><input type="submit" value="Назад"></p>
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