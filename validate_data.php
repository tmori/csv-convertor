<?php
require('utils/CsvFileIo.php');
require('utils/DataValidator.php');
require('utils/json_loader.php');

if (($argc != 3)) {
    print("USAGE: " . $argv[0] . " <validation_spec> <csv>\n");
    exit(1);
}

$validation_spec_file=$argv[1];
$csv=$argv[2];
$validation_spec = load_json($validation_spec_file);

$csv_obj = new CsvFileIo($csv);
$validator = new DataValidator($validation_spec, $csv_obj);
$ret = $validator->validate();
if ($ret == false) {
    printf("FAILED\n");
    exit(1);
}
else {
    printf("PASSED\n");
    exit(0);
}

?>