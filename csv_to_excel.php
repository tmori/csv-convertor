<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');
include('./project/vendor/autoload.php');
require('project/src/CsvToExcel.php');

if (($argc != 4) && ($argc != 5)) {
    printf("USAGE: %s <csv-file> <excel-file> <start-line> [dump-dir]\n", $argv[0]);
    return 1;
}
$src_csv=$argv[1];
$dst_xlsx=$argv[2];
$start_line=(int)$argv[3];
$tmp_array = explode('/', $src_csv);
$filename = $tmp_array[count($tmp_array) - 1];
$tmp_array = explode('.', $filename);
$sheet_name = $tmp_array[0];

$csv_obj = new CsvFileIo($src_csv);
$xlsx_obj = new CsvToExcel($dst_xlsx, $sheet_name);

$linenum = $csv_obj->linenum();
for ($row_inx = $start_line; $row_inx < $linenum; $row_inx++) {
    $record = $csv_obj->line($row_inx);
    $xlsx_obj->set_record($row_inx + 1, $record);
}


$xlsx_obj->save($dst_xlsx);
return 0;

?>