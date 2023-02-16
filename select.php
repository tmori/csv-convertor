<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 6) && ($argc != 7)) {
    print("USAGE: " . $argv[0] . " <csv-file> <colname> <cond> <cond_value> <start_line> [dump-dir]\n");
    exit(1);
}
$csv_file=$argv[1];
$colname=$argv[2];
$cond=$argv[3];
$cond_value=$argv[4];
$start_line=$argv[5];
$dump_path = "./dump.csv";
if ($argc == 7) {
    $tmp_array = explode('/', $csv_file);
    $last_entry = $tmp_array[count($tmp_array) -1];
    $tmp_array = explode('.', $last_entry);
    $filename = $tmp_array[0];
    $dump_path = $argv[6] . "/" . $filename . ".csv";
}

$csv_obj = new CsvFileIo($csv_file);
if (is_null($dump_path)) {
    $csv_obj->dump();
}
else {
    $csv_obj->dump($dump_path);
}
$select_obj = new CsvFileIo($dump_path);
$select_obj->splice_all($start_line);

$num = $csv_obj->linenum();
$colinx = $csv_obj->colinx($colname);
for ($i = $start_line; $i < $num; $i++) {
    $value = $csv_obj->value($i, $colinx);
    $is_hit = false;
    #printf("value=%s cond_value=%s cond=%s\n", $value, $cond_value, $cond);
    if (strcmp($cond, "eq") == 0) {
        if (strcmp("empty", $cond_value) == 0) {
            if (is_null($value) || empty($value)) {
                $is_hit = true;
                #printf("HIT: row=%d\n", $i);
            }
        }
        else {
            if (is_null($value) || empty($value)) {
            }
            else if (strcmp($value, $cond_value) == 0) {
                $is_hit = true;
                #printf("HIT: row=%d\n", $i);
            }
        }
    }
    else if (strcmp($cond, "ne") == 0) {
        if (strcmp("empty", $cond_value) == 0) {
            if (is_null($value) || empty($value)) {
            }
            else {
                $is_hit = true;
            }
        }
        else {
            if (is_null($value) || empty($value)) {
            }
            else if (strcmp($value, $cond_value) != 0) {
                $is_hit = true;
                #printf("HIT: row=%d\n", $i);
            }
        }
    }
    if ($is_hit) {
        $select_obj->insert($csv_obj->line($i));
    }
}

$select_obj->dump($dump_path);
exit(0);

?>