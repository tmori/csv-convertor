<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 3) && ($argc != 4)) {
    print("USAGE: " . $argv[0] . " <csv> <colinx> [dump-path]\n");
    exit(1);
}

$src_csv=$argv[1];
$colinx=$argv[2];
$dump_path = NULL;
if ($argc == 4) {
    $dump_path = $argv[3];
}
$src_csv_obj = new CsvFileIo($src_csv);
$src_csv_obj->del_col($colinx);
if (is_null($dump_path)) {
    $src_csv_obj->dump();
}
else {
    $src_csv_obj->dump($dump_path);
}
exit(0);

?>