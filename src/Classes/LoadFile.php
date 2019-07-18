<?php

namespace Export;

use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Shared\Date;

class LoadFile
{
    /**
     * Файл с формы $_FILES['file']
     *
     * @var bool|string
     */
    private $tmpFile;

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
    private function getExtension(): string
    {
        $file = explode('.', $this->tmpFile['name']);
        return array_pop($file);
    }

    /**
     * Открывает файл с расширением *.csv
     *
     * @return array таблица данных
     */
    private function csvToArr(): array
    {
        $table = explode(PHP_EOL, trim(file_get_contents($this->localFile)));
        $table = array_map(function ($value) {
            return explode(',', $value);
        }, $table);
        return $table;
    }

    /**
     * Открывает файл с расширением *.xls(x)
     *
     * @return array таблица данных
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function xlsToArr(): array
    {
        $spreadsheet = IOFactory::load($this->localFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $table = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $line = [];
            foreach ($row->getCellIterator() as $cell) {
                if(Date::isDateTime($cell)) {
                    $line[] = Date::excelToDateTimeObject($cell->getValue());
                } else {
                    $line[] = $cell->getValue();
                }
            }
            $table[] = $line;
        }
        return $table;
    }

    /**
     * Export constructor.
     * @param $file array загружаемый файл
     * @throws \Exception ошибки загрузки файла
     */
    public function __construct($file)
    {
        $this->tmpFile = $file;
        if ($this->tmpFile['error']) {
            throw new \Exception('File Download Error #' . $this->tmpFile['error']);
        }

        if (!is_uploaded_file($this->tmpFile['tmp_name'])) {
            throw new \Exception('Access denied');
        }

        if (mb_detect_encoding(file_get_contents($this->tmpFile['tmp_name'])) !== 'UTF-8') {
            throw new \Exception('Incorrect encoding. Use UTF-8');
        }

        $this->extension = $this->getExtension();
        $this->path = realpath(__DIR__ . '/../../storage/');

        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->localFile = $this->path . '/file.' . $this->extension;
    }

    /**
     * Возвращает информацию, считанную с файла
     *
     * @return array
     * @throws \Exception
     */
    public function getFileContents(): array
    {
        switch ($this->extension) {
            case 'xls':
            case 'xlsx':
                move_uploaded_file($this->tmpFile['tmp_name'], $this->localFile);
                return $this->xlsToArr();
                break;
            case 'csv':
                move_uploaded_file($this->tmpFile['tmp_name'], $this->localFile);
                return $this->csvToArr();
                break;
            default:
                throw new \Exception('Incorrect format. Use *.csv or *.xls(x)');
        }
    }

    /**
     * Возвращает названия полей из файла
     *
     * @return array
     * @throws \Exception
     */
    public function getNamesFields(): array
    {
        return $this->getFileContents()[0];
    }
}