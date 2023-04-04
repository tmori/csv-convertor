<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 4) && ($argc != 5)) {
    print("USAGE: " . $argv[0] . " <table-key-json> <old-csv> <new-csv> [dump-dir]\n");
    exit(1);
}

$pkey_json=$argv[1];
$old_csv=$argv[2];
$new_csv=$argv[3];
$dump_dir = ".";
if ($argc == 5) {
    $dump_dir = $argv[4];
}

$json_array = load_json($pkey_json);

$old_csv_obj = new CsvFileIo($old_csv);
$new_csv_obj = new CsvFileIo($new_csv);

print("INFO: OLD LINENUM=" . $old_csv_obj->linenum() . "\n");
print("INFO: OLD COLNUM=" . $old_csv_obj->colnum() . "\n");
print("INFO: NEW LINENUM=" . $new_csv_obj->linenum() . "\n");
print("INFO: NEW COLNUM=" . $new_csv_obj->colnum() . "\n");

if ($old_csv_obj->colnum() != $new_csv_obj->colnum()) {
    print("ERROR: old-csv's colnum != new-csv's colnum\n");
    return 1;
}
$old_csv_obj->shrink();
$new_csv_obj->shrink();

$old_csv_obj->create_cache($json_array["start_line"], $old_csv_obj->get_colinx_array($json_array["pkeys"]));
$new_csv_obj->create_cache($json_array["start_line"], $new_csv_obj->get_colinx_array($json_array["pkeys"]));

#$old_csv_obj->validate_pkeys($old_csv_obj->get_colinx_array($json_array["pkeys"]));
#$new_csv_obj->validate_pkeys($new_csv_obj->get_colinx_array($json_array["pkeys"]));

if (isset($json_array["exclude_cols"])) {
    $old_csv_obj->diff_with_exclude($old_csv_obj->get_colinx_array($json_array["pkeys"]), $json_array["start_line"], $new_csv_obj, $dump_dir, $json_array["exclude_cols"]);
}
else {
    $old_csv_obj->diff($old_csv_obj->get_colinx_array($json_array["pkeys"]), $json_array["start_line"], $new_csv_obj, $dump_dir);
}
exit(0);

?>