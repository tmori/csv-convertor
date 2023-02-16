<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 4) && ($argc != 5)) {
    print("USAGE: " . $argv[0] . " <csv> <colname> <start_line> [dump-path]\n");
    exit(1);
}

$src_csv=$argv[1];
$colname=$argv[2];
$start_line=(int)$argv[3];
$dump_path = NULL;
if ($argc == 5) {
    $dump_path = $argv[4];
}
$src_csv_obj = new CsvFileIo($src_csv);
$colinx = $src_csv_obj->colinx($colname);
$num = $src_csv_obj->linenum();

for ($i = $start_line; $i < $num; $i++) {
    $src_csv_obj->set_value($i, $colinx, "");
}

if (is_null($dump_path)) {
    $src_csv_obj->dump();
}
else {
    $src_csv_obj->dump($dump_path);
}
exit(0);

?>