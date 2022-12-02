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
    $src_pkey = $src_csv_obj->get_pkeys($i, $json_array["src"][$src_obj_name]["pkeys"]);
    $dst_csv_obj = $dst_objs[key($json_array["dsts"][0])];
    $dst_row = $dst_csv_obj->get_value_by_pkey_with_cache($src_pkey);
    #printf("dst_row=%s\n", $dst_row);
    if (is_null($dst_row)) {
        throw new Exception('ERROR: dst0 can not find pkey: ' . $src_pkey);
    }
    foreach ($json_array["conv_mapping"] as $value) {
        $conv_type = $value["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            $src_inx = $src_csv_obj->colinx($value["src"]);
            $dst_path = $value["dst_path"];
            $src_value = $src_csv_obj->value($i, $src_inx);
            $relation->set_value($dst_row, $dst_path, $src_value);
        }
        else if (strcmp($conv_type, "combine1") == 0) {
            $src_inx = $src_csv_obj->colinx($value["src"]);
            $dst_path = $value["dst_path"];
            $src_value = $src_csv_obj->value($i, $src_inx);
            $combined_value = sprintf(
                $value["combine_format"], 
                $src_value
            );
            $relation->set_value($dst_row, $dst_path, $combined_value);
        }
        else if (strcmp($conv_type, "combine2") == 0) {
            $src0_inx = $src_csv_obj->colinx($value["srcs"][0]);
            $src1_inx = $src_csv_obj->colinx($value["srcs"][1]);
            $dst_path = $value["dst_path"];
            $src0_value = $src_csv_obj->value($i, $src0_inx);
            $src1_value = $src_csv_obj->value($i, $src1_inx);
            $combined_value = sprintf(
                $value["combine_format"], 
                $src0_value, $src1_value
            );
            $relation->set_value($dst_row, $dst_path, $combined_value);
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