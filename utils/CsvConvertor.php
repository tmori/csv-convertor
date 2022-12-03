<?php

Class CsvConvertor
{
    function __construct()
    {
    }
    public function do_task($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $conv_type = $param["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            $this->conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "combine1") == 0) {
            $this->conv_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "combine2") == 0) {
            $this->conv_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
    private function conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_inx = $src_obj->colinx($param["src"]);
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_inx);
        $dst_obj->set_value($dst_row, $dst_path, $src_value);
    }
    private function conv_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_inx = $src_obj->colinx($param["src"]);
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_inx);
        $combined_value = sprintf(
            $param["combine_format"], 
            $src_value
        );
        $dst_obj->set_value($dst_row, $dst_path, $combined_value);
    }
    private function conv_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src0_inx = $src_obj->colinx($param["srcs"][0]);
        $src1_inx = $src_obj->colinx($param["srcs"][1]);
        $dst_path = $param["dst_path"];
        $src0_value = $src_obj->value($src_row, $src0_inx);
        $src1_value = $src_obj->value($src_row, $src1_inx);
        #printf("src0_value=%s src1_value=%s\n", $src0_value, $src1_value);
        $combined_value = sprintf(
            $param["combine_format"], 
            $src0_value, $src1_value
        );
        #printf("comvined_value=%s\n", $combined_value);
        $dst_obj->set_value($dst_row, $dst_path, $combined_value);
    }

}

?>