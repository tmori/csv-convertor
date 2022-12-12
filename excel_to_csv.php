<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');
include('./project/vendor/autoload.php');
require('project/src/ExcelToCsv.php');

if (($argc != 3) && ($argc != 4)) {
    printf("USAGE: %s <excel-file> <sheet_name> [<dump-path>]\n", $argv[0]);
    return 1;
}
$xlsx_file=$argv[1];
$sheet_name=$argv[2];
$dump_path = "./dump.csv";
if ($argc == 4) {
    $dump_path = $argv[3];
}

$csv_obj = new CsvFileIo(NULL);
$xlsx_obj = new ExcelToCsv($xlsx_file);
$xlsx_obj->load($sheet_name);
$cols = $xlsx_obj->get_record(1);
$csv_obj->set_cols($cols);

$row = 2;
while (true) {
    $record = $xlsx_obj->get_record($row);
    if (is_null($record)) {
        break;
    }
    $csv_obj->insert($record);
    $row++;
    #printf("row=%d\n", $row);
}

$csv_obj->dump($dump_path);

return 0;

?>