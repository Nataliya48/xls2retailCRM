<?php

use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;

class LoadFile
{
    /**
     * Расширение загружаемого файла
     *
     * @var bool|string
     */
    private $extension;

    /**
     * Директория, где хранится файл локально
     *
     * @var bool|string
     */
    private $path;

    /**
     * Полный путь к загруженному локальному файлу
     *
     * @var string
     */
    private $localFile;

    /**
     * Возвращает расширение файла (*.xls *.xlsx *.csv)
     *
     * @return string
     */
    private function getExtension($file): string
    {
        $file = explode('.', $file);
        return array_pop($file);
    }

    /**
     * Открывает файл с расширением *.csv
     *
     * @param $file загружаемый файл
     * @return array таблица данных
     */
    private function csvToArr($file)
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
    private function xlsToArr($file)
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

        $this->extension = $this->getExtension($file['name']);
        $this->path = realpath(__DIR__ . '/../storage/');

        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->localFile = $this->path . '/file.' . $this->extension;
    }

    public function getFileContents($file)
    {
        switch ($this->extension) {
            case 'xls':
            case 'xlsx':
                move_uploaded_file($file['tmp_name'], $this->localFile);
                return $this->xlsToArr($this->localFile);
                break;
            case 'csv':
                move_uploaded_file($file['tmp_name'], $this->localFile);
                return $this->csvToArr($this->localFile);
                break;
            default:
                throw new Exception('Incorrect format. Use *.csv or *.xls(x)');
        }
    }
}