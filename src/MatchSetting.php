<?php

Class MatchSetting
{
    /**
     * @var array
     */
    private $table;

    /**
     * MatchSetting constructor.
     * @param $table
     */
    function __construct($table)
    {
        $this->table = $table;
        //когда распарсили загруженный файл передаем сюда массив
        //получаем из массива первый элемент (первую строку)
        //возвращаем ее для выбора соответствий, а после нажатия на "подтвердить" передаем данные в SendRequest.php
        //учесть, что могут быть пустые столбцы (поля), нужно пропустить их
    }

    /**
     * Возвращает названия полей из файла
     *
     * @return mixed
     */
    public function getNamesFields()
    {
        return $this->table[0];
    }
}