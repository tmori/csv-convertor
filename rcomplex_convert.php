<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');
require('utils/CsvRelation.php');

if (($argc != 2) && ($argc != 3)) {
    print("USAGE: " . $argv[0] . " <json> [dump-path]\n");
    return 1;
}

$map_json=$argv[1];
$dump_path = NULL;
if ($argc == 3) {
    $dump_path = $argv[2];
}
$json_array = load_json($map_json);

$src_obj_name = key($json_array["src"]);
#printf("src_obj_name=%s\n", $src_obj_name);
#printf("INFO: src_obj_fpath=%s\n", $json_array["src"][$src_obj_name]["filepath"]);

$src_csv_obj = new CsvFileIo($json_array["src"][$src_obj_name]["filepath"]);
$src_csv_obj->create_cache(
    $json_array["src"][$src_obj_name]["start_line"], 
    $json_array["src"][$src_obj_name]["pkeys"]);
$dst_objs = array();
foreach ($json_array["dsts"] as $obj) {
    $dst_obj_name = key($obj);
    #printf("dst_obj_name=%s\n", $dst_obj_name);
    #printf("INFO: dst_obj_fpath=%s\n", $obj[$dst_obj_name]["filepath"]);
    $dst_csv_obj = new CsvFileIo($obj[$dst_obj_name]["filepath"]);
    $dst_objs[$dst_obj_name] = $dst_csv_obj;
    $dst_csv_obj->create_cache(
        $obj[$dst_obj_name]["start_line"], 
        $obj[$dst_obj_name]["pkeys"]);
}

$relation = new CsvRelation($json_array["dst_relations"], $dst_objs);

for ($i = $json_array["src"][$src_obj_name]["start_line"]; $i < $src_csv_obj->linenum(); $i++) {
    foreach ($json_array["conv_mapping"] as $value) {
        $conv_type = $value["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            $src_inx = $src_csv_obj->colinx($value["src"]);
            $dst_path = $value["dst_path"];
            $src_value = $src_csv_obj->value($i, $src_inx);
            $relation->set_value($i, $dst_path, $src_value);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
}

foreach ($json_array["dsts"] as $obj)
{
    $dst_obj_name = key($obj);
    if (is_null($dump_path)) {
        $path = "./" . $dst_obj_name . ".csv";
    }
    else {
        $path = $dump_path . "/" . $dst_obj_name . ".csv";
    }
    #printf("INFO: WRITING %s\n", $path);
    $dst_objs[$dst_obj_name]->dump($path);
}

?>