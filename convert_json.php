<?php
require('utils/CsvFileIo.php');
require('utils/DataValidator.php');

if (($argc != 3)) {
    print("USAGE: " . $argv[0] . " <csv> <out>\n");
    exit(1);
}

$csv=$argv[1];
$out=$argv[2];

$csv_obj = new CsvFileIo($csv);
$json_data = $csv_obj->to_json();

file_put_contents($out, $json_data);

?>