<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');


if (($argc != 4) && ($argc != 5)) {
    print("USAGE: " . $argv[0] . " <map-json> <src-csv> <dst-csv> [dump-path]\n");
    return 1;
}

$map_json=$argv[1];
$src_csv=$argv[2];
$dst_csv=$argv[3];
$dump_path = NULL;
if ($argc == 5) {
    $dump_path = $argv[4];
}

$json_array = load_json($map_json);

$src_csv_obj = new CsvFileIo($src_csv);
$dst_csv_obj = new CsvFileIo($dst_csv);

print("INFO: SRC LINENUM=" . $src_csv_obj->linenum() . "\n");
print("INFO: SRC COLNUM=" . $src_csv_obj->colnum() . "\n");
print("INFO: DST LINENUM=" . $dst_csv_obj->linenum() . "\n");
print("INFO: DST COLNUM=" . $dst_csv_obj->colnum() . "\n");

$range_start = $json_array["line_range"]["start"];
$range_end = $json_array["line_range"]["end"];
print("INFO: copy start line =" . strval($range_start) . "\n");
print("INFO: copy end   line =" . strval($range_end) . "\n");

for ($i = $range_start; $i <= $range_end; $i++) {
    foreach ($json_array["column_mapping"] as $value) {
        $src_inx = $value["src"];
        $dst_inx = $value["dst"];
        $src_value = $src_csv_obj->value($i, $src_inx);
        $dst_value = $dst_csv_obj->value($i, $dst_inx);
        print("INFO: COPYING src[" . $i . "][" . $src_inx .  "]='" . $src_value . "' >> ");
        print("dst[" . $i . "][" . $dst_inx .  "]='" . $dst_value . "'\n");
        $dst_csv_obj->set_value($i, $dst_inx, $src_value);
    }
}
if (is_null($dump_path)) {
    $dst_csv_obj->dump();
}
else {
    $dst_csv_obj->dump($dump_path);
}

?>