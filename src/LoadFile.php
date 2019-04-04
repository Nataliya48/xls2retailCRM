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
    public function getExtension($file): string
    {
        print_r(array_pop(explode(".", $file)));
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
        if ($file['error']) {
            throw new Exception('File Download Error #' . $file['error']);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Access denied');
        }

        if (mb_detect_encoding(file_get_contents($file['tmp_name'])) !== 'UTF-8') {
            throw new Exception('Incorrect encoding. Use UTF-8');
        }

        $extension = $this->getExtension($file['name']);
        $localFile = realpath(__DIR__ . '/../storage/') . '/file.' . $extension;

        switch ($extension) {
            case 'xls':
            case 'xlsx':
            move_uploaded_file($file, $localFile);
            $this->openXLS($localFile);
                break;
            case 'csv':
                move_uploaded_file($file, $localFile);
                $this->openCSV($localFile);
                break;
            default:
                throw new Exception('Incorrect format. Use *.csv or *.xls(x)');
        }
    }
}