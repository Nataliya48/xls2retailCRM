<?php

class Export
{
    //В конструкторе проверить корректность открываемого файла, его существование и права
    public function __construct()
    {
        if (is_uploaded_file($_FILES['event']['tmp_name'])) { // Определяет, был ли файл загружен при помощи HTTP POST
            file_get_contents($_FILES['event']['tmp_name']); // если да, то считать информацию с него
        } else {
            throw new Exception('Access denied'); // если файл загружен левым путем отказать в доступе
        }

        if (mb_detect_encoding(file_get_contents($_FILES['event']['tmp_name'])) !== 'UTF-8'){
            throw new Exception('Incorrect encoding. Use UTF-8'); // некорректная кодировка
        }
    }
}