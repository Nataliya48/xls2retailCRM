<?php

use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;

class LoadFile
{
    /**
     * Возвращает расширение файла (*.xls *.xlsx *.csv)
     *
     * @return string
     */
    private function getExtension($file): string
    {
        return array_pop(explode(".", $file));
    }

    /**
     * Открывает файл с расширением *.csv
     *
     * @param $file загружаемый файл
     * @return array таблица данных
     */
    private function openCSV($file)
    {
        $table = explode(PHP_EOL, trim(file_get_contents($file)));
        $table = array_map(function ($value) {
            return explode(',', $value);
        }, $table);
        return $table;
    }

    /**
     * Открывает файл с расширением *.xls(x)
     *
     * @param $file загружаемый файл
     * @return array таблица данных
     */
    private function openXLS($file)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $table = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $line = [];
            foreach ($row->getCellIterator() as $cell) {
                $line[] = $cell->getValue();
            }
            $table[] = $line;
        }
        return $table;
    }

    /**
     * Export constructor.
     * @throws Exception ошибки загрузки файла
     */
    public function __construct($file)
    {
        //В этом классе $file = $_FILES['event']['tmp_name'];
        if ($_FILES['file']['error']) {
            throw new Exception('File Download Error #' . $_FILES['file']['error']);
        }

        if (!is_uploaded_file($file)) {
            throw new Exception('Access denied');
        }

        if (mb_detect_encoding(file_get_contents($file)) !== 'UTF-8') {
            throw new Exception('Incorrect encoding. Use UTF-8');
        }

        move_uploaded_file($file, '/path/to/file.txt'); // указать директорию, куда сохранять файл

        switch ($this->getExtension($file)) {
            case 'xls':
            case 'xlsx':
                $this->openXLS($file);
                break;
            case 'csv':
                $this->openCSV($file);
                break;
            default:
                throw new Exception('Incorrect format. Use *.csv or *.xls(x)');
        }
    }
}