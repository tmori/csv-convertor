<?php
include('./project/vendor/autoload.php');
  
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelToCsv {
    private $excel_filepath;
    private $objSpreadsheet;
    private $objSheet;
    private $max_colnum = 255;
    private $colnum = 255;

    function __construct($excel_filepath)
    {
        $this->excel_filepath = $excel_filepath;
        $this->objSpreadsheet = IOFactory::load($this->excel_filepath);
    }
    public function load($sheet_name)
    {
        $this->colnum = $this->max_colnum;
        $this->objSheet = $this->objSpreadsheet->getSheetByName($sheet_name);
        for ($i = 0; $i < $this->max_colnum; $i++) {
            $value = $this->objSheet->getCellByColumnAndRow($i + 1, 1);
            if (is_null($value) || empty($value) || strcmp($value, "") == 0) {
                ;
            }
            else {
                $this->colnum = $i + 1;
            }
        }
        #printf("colnum=%d\n", $this->colnum);
    }

    public function get_record($row)
    {
        $record = array();
        $is_empty = true;
        for ($i = 0; $i < $this->colnum; $i++) {
            $value = $this->objSheet->getCellByColumnAndRow($i + 1, $row);
            if (is_null($value) || empty($value) || strcmp($value, "") == 0) {
                ;
            }
            else {
                #printf("value[%d]=%s\n", $i, $value);
                $is_empty = false;
            }
            array_push($record, $value);
        }
        if ($is_empty) {
            return NULL;
        }
        return $record;
    }

}
?>