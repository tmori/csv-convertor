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
printf("INFO: src_obj_fpath=%s\n", $json_array["src"][$src_obj_name]);

$src_csv_obj = new CsvFileIo($json_array["src"][$src_obj_name]);
$dst_objs = array();
foreach ($json_array["dsts"] as $obj) {
    $dst_obj_name = key($obj);
    printf("dst_obj_name=%s\n", $dst_obj_name);
    printf("INFO: dst_obj_fpath=%s\n", $obj[$dst_obj_name]["filepath"]);
    $dst_csv_obj = new CsvFileIo($obj[$dst_obj_name]["filepath"]);
    $dst_objs[$dst_obj_name] = $dst_csv_obj;
    $dst_csv_obj->create_cache(
        $obj[$dst_obj_name]["start_line"], 
        $obj[$dst_obj_name]["pkeys"]);
}

$relation = new CsvRelation($json_array["dst_relations"]);
$relation->get_value(1, $dst_objs, "out1.out2.out3", "email");
#$start_line_src = (int)$json_array["start_line_src"];
#$start_line_dst = (int)$json_array["start_line_dst"];
#$src_pkeys = $src_csv_obj->get_colinx_array($json_array["src_pkeys"]);
#$dst_pkeys = $dst_csv_obj->get_colinx_array($json_array["dst_pkeys"]);


#$dst_csv_obj->create_cache($start_line_dst, $dst_pkeys);


#if (is_null($dump_path)) {
#    $dst_csv_obj->dump();
#}
#else {
#    $dst_csv_obj->dump($dump_path);
#}

?>