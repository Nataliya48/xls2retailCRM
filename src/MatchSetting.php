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
        //возвращаем первую строку для выбора соответствий, а после нажатия на "подтвердить" передаем данные в SendRequest.php
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

    /*public function orderFormation()
    {
        unset($this->table[0]);
        foreach ($this->table as $row) {
            var_dump($row);
        }
    }*/


    // считать двумерный массив по строкам
    // строка как отдельный заказ
    // каждая строка отдельный запрос

    //для чего делается сопоставление
    //
}