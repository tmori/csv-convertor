<?php
include('./project/vendor/autoload.php');
  
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvToExcel {
    private $sheet_name;
    private $excel_filepath;
    private $objSpreadsheet;
    private $objSheet;

    function __construct($excel_filepath, $sheet_name)
    {
        $this->excel_filepath = $excel_filepath;
        $this->sheet_name = $sheet_name;
        $this->load();
    }
    private function load()
    {
        $this->objSpreadsheet = IOFactory::load($this->excel_filepath);
        $this->objSheet = $this->objSpreadsheet->getSheetByName($this->sheet_name);
    }

    public function set_record($row, $record)
    {
        $colnum = count($record);
        for ($i = 0; $i < $colnum; $i++) {
            $value = $record[$i];
            $this->objSheet->setCellValueByColumnAndRow($i+1, $row, $value);
        }
    }

    public function save($dump_filepath)
    {
        $objWriter = new Xlsx($this->objSpreadsheet);
        $objWriter->save($dump_filepath);
    }
}
?>