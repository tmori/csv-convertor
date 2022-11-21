<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 2) && ($argc != 3)) {
    print("USAGE: " . $argv[0] . " <csv> [dump-path]\n");
    return 1;
}

$src_csv=$argv[1];
$dump_path = NULL;
if ($argc == 3) {
    $dump_path = $argv[2];
}
$src_csv_obj = new CsvFileIo($src_csv);
#$src_csv_obj->add_double_quote();
if (is_null($dump_path)) {
    $src_csv_obj->dump_with_double_quote();
}
else {
    $src_csv_obj->dump_with_double_quote($dump_path);
}

?>