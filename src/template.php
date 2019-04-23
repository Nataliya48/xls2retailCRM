<html>
<head>
    <title>Export2CRM</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>

<h1 class="header-title">Export2CRM</h1>

<form enctype="multipart/form-data" method="post">
    <p>Загрузите файл</p>
    <p><input type="file" name="file" multiple
              accept="text/plain,application/excel,application/vnd.ms-excel,application/x-excel,application/x-msexcel">
        <input type="submit" value="Отправить"></p>
</form>

<form method="post">
    <p>Введите адрес retailCRM:</p>
    <input type="text" required name="url" placeholder="https://demo.retailcrm.ru"><br>
    <p>Введите API ключ:</p>
    <input type="text" required name="apiKey" placeholder="RiycYM83RnTR4dS7AKasJr0jtKpMe6j7"><br>
    <p><input type="submit" value="Отправить"></p>
</form>

<form method="post">
    <p>Что загружаем: </p>
    <p><select size="1" name="type[]">
            <option disabled>Что загружаем</option>
            <option value="orders">Заказы</option>
            <option value="customers">Клиенты</option>
        </select></p>

    <p>Выберите магазин: </p>
    <?php if (!empty($sites)): ?>
    <p><select size="1" name="site[]">
            <option disabled>Выберите магазин</option>
            <?php foreach ($sites as $code => $name): ?>
                <option value="<?= $code ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select></p>
    <p><input type="submit" value="Выбрать"></p>
</form>
<?php endif; ?>
<pre>
<?php //print_r(get_defined_vars()) ?>
</pre>

<table border="1" width="100%" cellpadding="5">
    <tr>
        <th width="50%">Поля из файла</th>
        <th width="50%">Поля из retailCRM</th>
    </tr>
    <tr>
        <td width="50%">поле из файла</td>
        <td width="50%">
            <select size="1" name="site[]">
                <?php foreach ($listFields as $code => $type): ?>
                    <?php if (is_array($type)): ?>
                        <?php foreach ($type as $keys => $fields): ?>
                            <option value="значение"><?= $fields ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="значение"><?= $type ?></option>
                    <?php endif;?>
                <?php endforeach; ?>
            </select>
        </td>
    <tr>
</table>

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

</body>