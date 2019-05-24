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
    <form method="post" class="formMapp" action="index.php?action=start">
    <?php if ($errorMassage !== null): ?>
        <p class="load">Во время загрузки возникли следующие ошибки:</p><br>
        <?php foreach ($errorMassage as $massage): ?>
            <p><?php echo $massage ?></p>
        <?php endforeach; ?>
        <br>
    <?php else: ?>
        <p class="load">Загрузка успешно завершена.</p>
    <?php endif; ?>
        <p class="load">Выполнить повторную загрузку?</p>
        <p><input type="submit" value="Вернуться"></p>
    </form>
</div>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya <a href="documentation.php">Документация</a></p>
</footer>

</body>