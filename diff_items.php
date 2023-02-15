<?php
require('utils/json_loader.php');
require('utils/CsvFileIo.php');
require('utils/CsvRelation.php');
require('utils/CsvDiffers.php');

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
$src_obj_name = key($json_array["srcs"]);
#printf("src_obj_name=%s\n", $src_obj_name);
#printf("INFO: src_obj_fpath=%s\n", $json_array["src"][$src_obj_name]["filepath"]);

$src_objs = array();
foreach ($json_array["srcs"] as $obj) {
    $src_obj_name = key($obj);
    #printf("src=%s\n", $src_obj_name);
    $env_filepath = sprintf("SRCS_%s_FILEPATH", strtoupper($src_obj_name));
    $filepath = getenv($env_filepath, $local_only = true);
    #printf("filepath=%s\n", $filepath);
    #printf("is_null=%d\n", is_null($filepath));
    #printf("empty=%d\n", empty($filepath));
    #printf("isset=%d\n", isset($filepath));
    if (($filepath === false) || empty($filepath)) {
        #printf("filepath=%s\n", $obj[$src_obj_name]["filepath"]);
        $src_csv_obj = new CsvFileIo($obj[$src_obj_name]["filepath"]);
    }
    else {
        $src_csv_obj = new CsvFileIo($filepath);
    }
    $src_objs[$src_obj_name] = $src_csv_obj;
    $src_csv_obj->create_cache(
        $obj[$src_obj_name]["start_line"], 
        $src_csv_obj->get_colinx_array($obj[$src_obj_name]["pkeys"]));
}

$dst_objs = array();
$dst_pkeys = NULL;
foreach ($json_array["dsts"] as $obj) {
    $dst_obj_name = key($obj);
    #printf("dst_obj_name=%s\n", $dst_obj_name);
    #printf("INFO: dst_obj_fpath=%s\n", $obj[$dst_obj_name]["filepath"]);
    $env_filepath = sprintf("DSTS_%s_FILEPATH", strtoupper($dst_obj_name));
    $filepath = getenv($env_filepath, $local_only = true);
    if (($filepath === false) || empty($filepath)) {
        $dst_csv_obj = new CsvFileIo($obj[$dst_obj_name]["filepath"]);
    }
    else {
        $dst_csv_obj = new CsvFileIo($filepath);
    }
    $dst_objs[$dst_obj_name] = $dst_csv_obj;
    $dst_csv_obj->create_cache(
        $obj[$dst_obj_name]["start_line"], 
        $dst_csv_obj->get_colinx_array($obj[$dst_obj_name]["pkeys"]));
    if (is_null($dst_pkeys)) {
        $dst_pkeys = $dst_csv_obj->get_colinx_array($obj[$dst_obj_name]["pkeys"]);
    }
}

$relation_src = new CsvRelation($json_array["src_relations"], $src_objs);
$relation_dst = new CsvRelation($json_array["dst_relations"], $dst_objs);

$differs = new CsvDiffers();

$obj = $json_array["srcs"][0];
$src_csv_obj = $src_objs[key($obj)];
$src_linenum = $src_csv_obj->linenum();
$src_obj_name = key($obj);
$src_start_line = $obj[$src_obj_name]["start_line"];
$src_pkeys = $src_csv_obj->get_colinx_array($obj[$src_obj_name]["pkeys"]);

$dst_csv_obj = $dst_objs[key($json_array["dsts"][0])];
$relation_dst->set_root(key($json_array["dsts"][0]));
#printf("linenum=%d\n", $dst_csv_obj->linenum());
$ret_value = 0;
for ($src_row = $src_start_line; $src_row < $src_linenum; $src_row++) {
    $src_pkey = $src_csv_obj->get_pkeys($src_row, $src_pkeys);
    $dst_row = $dst_csv_obj->get_value_by_pkey_with_cache($src_pkey);
    #printf("dst_row=%s src_pkey=%s linenum=%d\n", $dst_row, $src_pkey, $dst_csv_obj->linenum());
    if (is_null($dst_row)) {
        throw new Exception('ERROR: dst0 can not find pkey: ' . $src_pkey);
    }
    else if (is_null($dst_row)) {
        throw new Exception('ERROR: dst0 can not find pkey: ' . $src_pkey);
    }

    foreach ($json_array["params"] as $param) {
        $ret = $differs->do_diff($param, $relation_src, $src_row, $relation_dst, $dst_row);
        if ($ret != 0) {
            $ret_value = 1;
        }
    }
}

return $ret_value;

?>