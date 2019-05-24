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
    <form method="post" class="formMapp" action="index.php?action=connect">
        <p class="load">Выставите соответствие для магазина и сущности, которую требуется загрузить в retailCRM, из списков. </p>
        <table border="0" width="100%" cellpadding="5">

            <tr>
                <td width="50%" align="right">
                    Что загружаем:
                </td>
                <td width="50%">
                    <select size="1" name="type">
                        <option selected disabled>Что загружаем</option>
                        <option value="orders">Заказы</option>
                        <option value="customers">Клиенты</option>
                    </select>
                </td>

            <tr>
            <tr>
                <td width="50%" align="right">
                    Выберите магазин:
                </td>
                <td width="50%">
                    <?php if (!empty($sites)): ?>
                        <select size="1" name="site">
                                <option selected disabled>Выберите магазин</option>
                                <?php foreach ($sites as $code => $name): ?>
                                    <option value="<?= $code ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                    <?php endif; ?>
                </td>

            <tr>

        </table>

        <p><input type="submit" value="Отправить"></p>
    </form>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<footer class="footer">
    <p class="foot">©Nataliya <a href="documentation.php">Документация</a></p>
</footer>

</body>