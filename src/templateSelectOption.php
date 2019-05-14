<?php session_start();?>
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
    <form method="post" class="form" action="index.php?action=connect">
        <p>Что загружаем: </p>
        <p><select size="1" name="type">
                <option disabled>Что загружаем</option>
                <option value="orders">Заказы</option>
                <option value="customers">Клиенты</option>
            </select></p>

        <p>Выберите магазин: </p>
        <?php if (!empty($sites)): ?>
        <p><select size="1" name="site">
                <option disabled>Выберите магазин</option>
                <?php foreach ($sites as $code => $name): ?>
                    <option value="<?= $code ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select></p>
        <?php endif; ?>
        <p><input type="submit" value="Отправить"></p>
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