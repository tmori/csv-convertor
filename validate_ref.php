<?php

require('utils/json_loader.php');
require('utils/CsvFileIo.php');


if ($argc != 4) {
    print("USAGE: " . $argv[0] . " <map-json> <parent-csv> <child-csv>\n");
    exit(1);
}

$pkey_json=$argv[1];
$parent_csv=$argv[2];
$child_csv=$argv[3];

$json_array = load_json($pkey_json);

$parent_csv_obj = new CsvFileIo($parent_csv);
$child_csv_obj = new CsvFileIo($child_csv);

$start_line_parent = $json_array["start_line_parent"];
$start_line_child = $json_array["start_line_child"];
$parent_pkey_cols = $parent_csv_obj->get_colinx_array($json_array["parent_pkey_cols"]);
$child_fkey_cols = $child_csv_obj->get_colinx_array($json_array["child_fkey_cols"]);
$child_dst_col = $child_csv_obj->colinx($json_array["child_dst_col"]);

$parent_csv_obj->create_cache($start_line_parent, $parent_pkey_cols);

$success = true;
for ($i = $start_line_child; $i < $child_csv_obj->linenum(); $i++) {
    $keyword = $child_csv_obj->get_pkeys($i, $child_fkey_cols);
    if ($keyword) {
        $row_id = $parent_csv_obj->get_value_by_pkey_with_cache($keyword);
        if ($row_id) {
            //NOP
        }
        else {
            $success = false;
            $reason = "FILE: " . $parent_csv . " does not have reference data";
            printf("FAILED, line number: %4d, colname: %s, reason: %s\n", $i + $start_line_parent, $keyword, $reason);
        }
    }
    else {
        $success = false;
        printf("ERROR: not found child_fkey_cols:row=%d child_fkey_cols=%s\n", $i + $start_line_child, $child_fkey_cols[0]);
    }
}

if ($success) {
    printf("PASSED\n");
    exit(0);
}
else {
    printf("FAILED\n");
    exit (1);
}

?>