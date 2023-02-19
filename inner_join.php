<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 4) && ($argc != 5)) {
    print("USAGE: " . $argv[0] . " <map-json> <parent-csv> <child-csv> [dump-path]\n");
    exit(1);
}

$pkey_json=$argv[1];
$parent_csv=$argv[2];
$child_csv=$argv[3];
$dump_path = NULL;
if ($argc == 5) {
    $dump_path = $argv[4];
}

$json_array = load_json($pkey_json);

$parent_csv_obj = new CsvFileIo($parent_csv);
$child_csv_obj = new CsvFileIo($child_csv);

$start_line_parent = $json_array["start_line_parent"];
$start_line_child = $json_array["start_line_child"];
$parent_pkey_cols = $parent_csv_obj->get_colinx_array($json_array["parent_pkey_cols"]);

$child_fkey_cols = $child_csv_obj->get_colinx_array($json_array["child_fkey_cols"]);
$child_csv_obj->add_col($json_array["child_dst_col"]);
$child_dst_col = $child_csv_obj->colinx($json_array["child_dst_col"]);

$parent_csv_obj->create_cache($start_line_parent, $parent_pkey_cols);

for ($i = $start_line_child; $i < $child_csv_obj->linenum(); $i++) {
    $keyword = $child_csv_obj->get_pkeys($i, $child_fkey_cols);
    #printf("keyword=%s\n", $keyword);
    if ($keyword) {
        $row_id = $parent_csv_obj->get_value_by_pkey_with_cache($keyword);
        #$row_id = $parent_csv_obj->get_value_by_pkey($start_line_parent, $parent_pkey_cols, $keyword);
        if ($row_id) {
            $src_value = $parent_csv_obj->value($row_id, $parent_csv_obj->colinx($json_array["parent_src_col"]));
            $child_csv_obj->set_value($i, $child_dst_col, $src_value);
        }
        else {
            printf("WARN: not found parent_pkey_cols:row=%d: keyword=%s\n", $i, $keyword);
        }
    }
    else {
        printf("WARN: not found child_fkey_cols:row=%d child_fkey_cols=%s\n", $i, $child_fkey_cols[0]);
    }
}


if (is_null($dump_path)) {
    $child_csv_obj->dump();
}
else {
    $child_csv_obj->dump($dump_path);
}

exit(0);

?>