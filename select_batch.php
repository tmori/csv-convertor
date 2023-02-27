<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 5) && ($argc != 6)) {
    print("USAGE: " . $argv[0] . " <csv-file> <colname> <cond_values_file> <start_line> [dump-dir]\n");
    exit(1);
}
$csv_file=$argv[1];
$colname=$argv[2];
$cond_values_file=$argv[3];
$start_line=$argv[4];
$dump_path = "./dump.csv";
if ($argc == 6) {
    $tmp_array = explode('/', $csv_file);
    $last_entry = $tmp_array[count($tmp_array) -1];
    $tmp_array = explode('.', $last_entry);
    $filename = $tmp_array[0];
    $dump_path = $argv[5] . "/" . $filename . ".csv";
}

$csv_obj = new CsvFileIo($csv_file);
if (is_null($dump_path)) {
    $csv_obj->dump();
}
else {
    $csv_obj->dump($dump_path);
}
$csv_obj->create_cache($start_line, $csv_obj->get_colinx_array([$colname]));

$select_obj = new CsvFileIo($dump_path);
$select_obj->splice_all($start_line);

$cond_values_csv_obj = new CsvFileIo($cond_values_file);
$cond_values_num = $cond_values_csv_obj->linenum();

for ($k = 0; $k < $cond_values_num; $k++) {
    $cond_value = $cond_values_csv_obj->value($k, 0);
    #printf("cond_value=%s\n", $cond_value);
    $num = $csv_obj->linenum();
    $colinx = $csv_obj->colinx($colname);

    $key = $cond_values_csv_obj->get_pkeys($k, [0]);
    $row = $csv_obj->get_value_by_pkey_with_cache($key);
    if ($row) {
        $select_obj->insert($csv_obj->line($row));
    }
}

$select_obj->dump($dump_path);
exit(0);

?>