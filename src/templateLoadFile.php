<html>
<head>
    <link rel="stylesheet" href="style.css" type="text/css">
    <title align="center">Export2CRM</title>
</head>
<body>

<h1 class="header-title">Export2CRM</h1>
<div>
    <form enctype="multipart/form-data" method="post">
        <p>Загрузите файл</p>
        <p><input type="file" name="file" multiple
                  accept="text/plain,application/excel,application/vnd.ms-excel,application/x-excel,application/x-msexcel">
            <input type="submit" value="Отправить"></p>
    </form>
</div>
<?php
if (isset($errorMsg)):
    echo $errorMsg;
endif;
?>

</body>