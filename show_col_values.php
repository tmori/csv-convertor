<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if ($argc != 4) {
    print("USAGE: " . $argv[0] . " <csv-file> <colname> <start_line>\n");
    exit(1);
}
$csv_file=$argv[1];
$colname=$argv[2];
$start_line=$argv[3];

$csv_obj = new CsvFileIo($csv_file);
$num = $csv_obj->linenum();
$colinx = $csv_obj->colinx($colname);
for ($i = $start_line; $i < $num; $i++) {
    $value = $csv_obj->value($i, $colinx);
    print(strval($value). "\n");
}

exit(0);

?>