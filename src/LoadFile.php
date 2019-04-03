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
    private function getExtension(): string
    {
        return array_pop(explode(".", $_FILES['event']['tmp_name']));
    }

    private function convertToCSV()
    {
        if ($this->getExtension() === 'xls' && $this->getExtension() === 'xlsx'){
            // конвертируется в csv
            /*$reader = new Xlsx();
            $spreadsheet = $reader->load($_FILES['event']['tmp_name']);
            $loadedSheetNames = $spreadsheet->getSheetNames();

            $writer = new Csv($spreadsheet);

            foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $writer->setSheetIndex($sheetIndex);
                $writer->save($loadedSheetName.'.csv');
            }*/
            //return $loadedSheetName.'.csv';

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['event']['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $row = $worksheet->getRowIterator()->current();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $key => $cell) {
                $arrNamesCol[] = $cell->getValue(); // записываем названия первых ячеек колонок в отдельный массив
                $namesCol = implode(';', $arrNamesCol);
            }

            foreach ($worksheet->getRowIterator(2) as $row) {   //начиная со второй строки получить инфу по строкам
                foreach ($row->getCellIterator() as $key => $cell){
                    $arrValueCell[$key][] = $cell->getValue();
                    $valueCell = implode(';', $arrValueCell);
                }
            }
            return [$namesCol, $valueCell];
        } elseif ($this->getExtension() === 'csv') {
            return file_get_contents($_FILES['event']['tmp_name']); // иначе возвращает исходный файл
        } else {
            throw new Exception('Incorrect format. Use *.csv or *.xls(x)');

        }
    }

    /**
     * Export constructor.
     * @throws Exception ошибки загрузки файла
     */
    public function __construct($file)
    {
        if ($_FILES['file']['error']) {
            throw new Exception('File Download Error #' . $_FILES['file']['error']);
        }

        if (is_uploaded_file($_FILES['event']['tmp_name'])) {
            $this->convertToCSV();
        } else {
            throw new Exception('Access denied');
        }

        if (mb_detect_encoding(file_get_contents($_FILES['event']['tmp_name'])) !== 'UTF-8') {
            throw new Exception('Incorrect encoding. Use UTF-8');
        }
    }
}