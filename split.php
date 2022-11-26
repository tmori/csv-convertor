<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');

if (($argc != 4) && ($argc != 5)) {
    printf("USAGE: %s <split-linenum> <start-line> <csv-file> [dump-dir]\n", $argv[0]);
    return 1;
}

$split_linenum=(int)$argv[1];
$start_line=(int)$argv[2];
$csv_file=$argv[3];
$dump_dir = ".";
if ($argc == 5) {
    $dump_dir = $argv[4];
}

$csv_obj = new CsvFileIo($csv_file);

#print("INFO: LINENUM=" . $csv_obj->linenum() . "\n");
#print("INFO: COLNUM=" . $csv_obj->colnum() . "\n");

$total_linenum = $csv_obj->linenum() - $start_line;
if ($total_linenum < 0) {
    printf("ERROR: total_linenum is invalid: %d\n", $total_linenum);
    return 1;
}

$filenum = (int)(($total_linenum + ($split_linenum - 1)) / $split_linenum);
#printf("total_linenum=%d\n", $total_linenum);
#printf("filenum=%d\n", $filenum);

$tmp_array = explode('/', $csv_file);
$last_entry = $tmp_array[count($tmp_array) -1];
$tmp_array = explode('.', $last_entry);
$filename = $tmp_array[0];

for ($i = 0; $i < $filenum; $i++) {
    $s_file = sprintf("%s/%s-%d.csv", $dump_dir, $filename, $i);
    $csv_obj->dump($s_file);
    $new_obj = new CsvFileIo($s_file);

    //cut head
    if ($i != 0) {
        $cut_head_num = ($split_linenum * $i);
        $new_obj->splice($start_line, $cut_head_num);
        #printf("linenum=%d\n", $new_obj->linenum());
    }
    //cut tail
    $tail_start_line = $start_line + $split_linenum; 
    $new_obj->splice_all($tail_start_line);

    $new_obj->dump($s_file);
    #printf("INFO: CREATED %s\n", $s_file);
}

return 0;

?>