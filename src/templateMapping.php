<?php session_start();?>
<?php $listFields = $_SESSION['listFields'];?>
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
    <table border="1" width="100%" cellpadding="5">
        <tr>
            <th width="50%">Поля из файла</th>
            <th width="50%">Поля из retailCRM</th>
        </tr>
        <tr>

            <?php if (!empty($fieldsFile)):  ?>
                <?php foreach ($fieldsFile as $field): ?>
                    <td width="50%"><?= $field ?></td>
                <?php endforeach; ?>
            <?php endif;?>

            <td width="50%">
                <select size="1" name="site[]">
                    <?php if (!empty($listFields)): ?>
                        <?php foreach ($listFields as $code => $type): ?>
                            <?php if (is_array($type)): ?>
                                <?php foreach ($type as $keys => $fields): ?>
                                    <option value="<?= $keys ?>"><?= $fields ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="<?= $code ?>"><?= $type ?></option>
                            <?php endif;?>
                        <?php endforeach; ?>
                    <?php endif;?>
                </select>
            </td>

        <tr>
    </table>
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