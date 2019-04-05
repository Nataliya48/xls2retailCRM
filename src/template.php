<html>
<head>
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

<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

<?php if (!empty($fields)): ?>
<p><select size="1" name="recipe">
    <?php foreach ($fields as $field): ?>
        <p><?= $field ?><option value="<?= $field ?>"><?= $field ?></option><p>
    <?php endforeach; ?>
</select></p>
<?php endif; ?>

</body>