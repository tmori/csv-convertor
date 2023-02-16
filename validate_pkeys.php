<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 3)) {
    print("USAGE: " . $argv[0] . " <table-key-json> <csv>\n");
    exit(1);
}

$pkey_json=$argv[1];
$csv=$argv[2];

$json_array = load_json($pkey_json);

$csv_obj = new CsvFileIo($csv);

$csv_obj->create_cache($json_array["start_line"], $csv_obj->get_colinx_array($json_array["pkeys"]));
$csv_obj->validate_pkeys($csv_obj->get_colinx_array($json_array["pkeys"]));

exit(0);

?>