<?php
require('utils/CsvFileIo.php');
require('utils/DataValidator.php');

if (($argc != 3)) {
    print("USAGE: " . $argv[0] . " <validation_spec> <csv>\n");
    exit(1);
}

$validation_spec=$argv[1];
$csv=$argv[2];

$csv_obj = new CsvFileIo($csv);
$validator = new DataValidator($validation_spec, $csv_obj);
$ret = $validator->validate();
if ($ret == false) {
    printf("FAILED\n");
}
else {
    printf("PASSED\n");
}
exit($ret)
?>