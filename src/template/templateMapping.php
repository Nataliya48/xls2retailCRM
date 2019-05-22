<?php

session_start();
$type = $_SESSION['type'];
$listFieldsCrm = $_SESSION['listFieldsCrm'][$type];
$fieldsFileLoad = $_SESSION['fieldsFileLoad'];
$customFields = $_SESSION['customFields'];

?>

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

    <form method="post" class="formMapp" action="index.php?action=mapping">
        <p class="load">Выставите соответствие для полей из загруженного файла, для полей из retaiLCRM (в какие поля требуется загрузить информацию).</p>
    <table border="0" width="100%" cellpadding="5">
        <tr>
            <th width="50%" align="right">Поля из файла</th>
            <th width="50%" align="left">Поля из retailCRM</th>
        </tr>
        <?php if (!empty($fieldsFileLoad)):  ?>
        <?php foreach ($fieldsFileLoad as $field): ?>
        <tr>

            <td width="50%" align="right"><input type="hidden" name="file[]" value="<?= $field ?>"><?= $field ?></td>

            <td width="50%">
                <select size="1" name="crm[]">
                    <?php if (!empty($listFieldsCrm)): ?>
                        <?php foreach ($listFieldsCrm as $code => $type): ?>
                            <option value="<?= $code ?>"><?= $type ?></option>
                        <?php endforeach; ?>
                    <?php endif;?>
                    <?php if (!empty($customFields)): ?>
                        <?php foreach ($customFields as $code => $type): ?>
                            <option value="<?= 'customFields.' . $code ?>"><?= $type ?></option>
                        <?php endforeach; ?>
                    <?php endif;?>
                </select>
            </td>

        <tr>
            <?php endforeach; ?>
            <?php endif;?>
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