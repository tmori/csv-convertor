<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 4) && ($argc != 5)) {
    printf("USAGE: %s <src-csv> <merge-csv> <merge-csv-start-line> [dump-dir]\n", $argv[0]);
    exit(1);
}

$src_csv_file=$argv[1];
$merge_csv_file=$argv[2];
$merge_csv_start_line=(int)$argv[3];
$dump_path = "./dump.csv";
if ($argc == 5) {
    $tmp_array = explode('/', $src_csv_file);
    $last_entry = $tmp_array[count($tmp_array) -1];
    $tmp_array = explode('.', $last_entry);
    $filename = $tmp_array[0];
    $dump_path = $argv[4] . "/" . $filename . ".csv";
}

$src_csv_obj = new CsvFileIo($src_csv_file);
$merge_csv_obj = new CsvFileIo($merge_csv_file);
if ($src_csv_obj->colnum() != $merge_csv_obj->colnum()) {
    print("ERROR: src-csv's colnum != merge-csv's colnum\n");
    return 1;
}

$num = $merge_csv_obj->linenum();
for ($i = $merge_csv_start_line; $i < $num; $i++) {
    $src_line = $merge_csv_obj->line($i);
    $src_csv_obj->insert($src_line);
}

if (is_null($dump_path)) {
    $src_csv_obj->dump();
}
else {
    $src_csv_obj->dump($dump_path);
}
exit(0);

?>