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

$start_line_src = (int)$json_array["start_line_src"];
$start_line_dst = (int)$json_array["start_line_dst"];
$src_pkeys = $src_csv_obj->get_colinx_array($json_array["src_pkeys"]);
$dst_pkeys = $dst_csv_obj->get_colinx_array($json_array["dst_pkeys"]);

print("INFO: SRC LINENUM=" . $src_csv_obj->linenum() . "\n");
print("INFO: SRC COLNUM=" . $src_csv_obj->colnum() . "\n");
print("INFO: DST LINENUM=" . $dst_csv_obj->linenum() . "\n");
print("INFO: DST COLNUM=" . $dst_csv_obj->colnum() . "\n");

$serial_index = 0;
for ($i = $start_line_src; $i < $src_csv_obj->linenum(); $i++) {
    $src_pkey = $src_csv_obj->get_pkeys($i, $src_pkeys);
    $dst_row = $dst_csv_obj->get_value_by_pkey($start_line_dst, $dst_pkeys, $src_pkey);
    if (is_null($dst_row)) {
        $line = $dst_csv_obj->get_empty_line();
        $p_inx = 0;
        foreach ($dst_pkeys as $dst_pkey) {
            $v = $src_csv_obj->value($i, $src_pkeys[$p_inx]);
            $line[$dst_pkey] = $v;
            $p_inx++;
        }
        $dst_csv_obj->insert($line);
        $dst_row = $dst_csv_obj->get_value_by_pkey($start_line_dst, $dst_pkeys, $src_pkey);
    }
    foreach ($json_array["conv_mapping"] as $value) {
        $conv_type = $value["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            $src_inx = $src_csv_obj->colinx($value["src"]);
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            $src_value = $src_csv_obj->value($i, $src_inx);
            $dst_value = $dst_csv_obj->value($i, $dst_inx);
            print("INFO: COPYING src[" . $i . "][" . $src_inx .  "]='" . $src_value . "' >> ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $dst_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $src_value);
        }
        else if (strcmp($conv_type, "fixed") == 0) {
            $dst_value = $value["value"];
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            print("INFO: SETTING ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $dst_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $dst_value);
        }
        else if (strcmp($conv_type, "serial") == 0) {
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            $dst_value = $value["initial_value"] + $serial_index;
            print("INFO: SETTING ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $dst_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $dst_value);
        }
        else if (strcmp($conv_type, "split") == 0) {
            $src_inx = $src_csv_obj->colinx($value["src"]);
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            $src_value = $src_csv_obj->value($i, $src_inx);
            $dst_value = $dst_csv_obj->value($i, $dst_inx);
            $split_values = explode($value["split_key"], $src_value);
            $split_value = $split_values[$value["split_index"]];
            print("INFO: COPYING src[" . $i . "][" . $src_inx .  "]='" . $src_value . "' >> ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $split_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $split_value);
        }
        else if (strcmp($conv_type, "combine") == 0) {
            $src0_inx = $src_csv_obj->colinx($value["srcs"][0]);
            $src1_inx = $src_csv_obj->colinx($value["srcs"][1]);
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            $src0_value = $src_csv_obj->value($i, $src0_inx);
            $src1_value = $src_csv_obj->value($i, $src1_inx);
            $combined_value = sprintf(
                $value["combine_format"], 
                $src_csv_obj->value($i, $src0_inx),
                $src_csv_obj->value($i, $src1_inx)
            );
            print("INFO: COPYING src[" . $i . "][" . $src0_inx .  "]='" . $src0_value . "' && ");
            print("INFO: COPYING src[" . $i . "][" . $src1_inx .  "]='" . $src1_value . "' >> ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $combined_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $combined_value);
        }
        else if (strcmp($conv_type, "combine3") == 0) {
            $src0_inx = $src_csv_obj->colinx($value["srcs"][0]);
            $src1_inx = $src_csv_obj->colinx($value["srcs"][1]);
            $src2_inx = $src_csv_obj->colinx($value["srcs"][2]);
            $dst_inx = $dst_csv_obj->colinx($value["dst"]);
            $src0_value = $src_csv_obj->value($i, $src0_inx);
            $src1_value = $src_csv_obj->value($i, $src1_inx);
            $src2_value = $src_csv_obj->value($i, $src2_inx);
            $combined_value = sprintf(
                $value["combine_format"], 
                $src_csv_obj->value($i, $src0_inx),
                $src_csv_obj->value($i, $src1_inx),
                $src_csv_obj->value($i, $src2_inx)
            );
            print("INFO: COPYING src[" . $i . "][" . $src0_inx .  "]='" . $src0_value . "' && ");
            print("INFO: COPYING src[" . $i . "][" . $src1_inx .  "]='" . $src1_value . "' && ");
            print("INFO: COPYING src[" . $i . "][" . $src2_inx .  "]='" . $src2_value . "' >> ");
            print("dst[" . $dst_row . "][" . $dst_inx .  "]='" . $combined_value . "'\n");
            $dst_csv_obj->set_value($dst_row, $dst_inx, $combined_value);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
    $serial_index++;
}


if (is_null($dump_path)) {
    $dst_csv_obj->dump();
}
else {
    $dst_csv_obj->dump($dump_path);
}

?>