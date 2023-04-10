<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 3) && ($argc != 4)) {
    print("USAGE: " . $argv[0] . " <table-key-json> <csv> [skip_empty]\n");
    exit(1);
}

$pkey_json=$argv[1];
$csv=$argv[2];
$skip_empty = false;
if ($argc == 4) {
    $skip_empty = true;
}

$json_array = load_json($pkey_json);

$csv_obj = new CsvFileIo($csv);

#$csv_obj->create_cache($json_array["start_line"], $csv_obj->get_colinx_array($json_array["pkeys"]));

$error_list = array();
$success = $csv_obj->validate_pkeys($csv_obj->get_colinx_array($json_array["pkeys"]), $error_list, $skip_empty);

if ($success) {
    printf("PASSED\n");
    exit(0);
}
else {
    printf("FAILED\n");
    print_r($error_list);
    exit(1);
}

?>