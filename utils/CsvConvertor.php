<?php

Class CsvConvertor
{
    private $serial_id;
    function __construct()
    {
        $this->serial_id = 0;
    }
    public function do_task($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $conv_type = $param["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            $this->conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "fixed") == 0) {
            $this->conv_fixed($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "serial") == 0) {
            $this->conv_serial($param, $src_obj, $src_row, $dst_obj, $dst_row);
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
    private function conv_fixed($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $value =$param["value"];
        $dst_path = $param["dst_path"];

        $dst_obj->set_value($dst_row, $dst_path, $value);
    }
    private function conv_serial($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $dst_path = $param["dst_path"];
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        if ($dst_value) {
            $this->serial_id = $dst_value;
        }
        else {
            $dst_value = $dst_obj->last_value_by_root($dst_path, 1);
            #printf("serial_id=%d dst_row=%d dst_value=%s\n", $this->serial_id, $dst_row, $dst_value);
            if ($dst_value) {
                $this->serial_id = $dst_value + 1;
            }
            else {
                if ($this->serial_id == 0) {
                    $this->serial_id = (int)$param["initial_value"];
                }
                else {
                    $this->serial_id = $this->serial_id + 1;
                }
            }
            #printf("dst_row=%d dst_value=%d serial_id=%d\n", $dst_row, $dst_value, $this->serial_id);
            $dst_obj->set_value($dst_row, $dst_path, $this->serial_id);
        }
        #printf("serial_value=%s\n", $dst_obj->value($dst_row, $dst_path));
    }
    private function conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];

        if (isset($param["calc_src_row"]) && $param["calc_src_row"]) {
            $pkey = $dst_obj->pkey($dst_row, $dst_path);
            $src_row = $src_obj->row_by_root($src_path, $pkey);
        }
        $src_value = $src_obj->value($src_row, $src_path);
        #printf("src_path=%s src_value=%s\n", $src_path, $src_value);
        $dst_obj->set_value($dst_row, $dst_path, $src_value);
        #printf("dst_value=%s\n", $dst_obj->value($dst_row, $dst_path));
    }
    private function conv_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_path);
        $combined_value = sprintf(
            $param["combine_format"], 
            $src_value
        );
        $dst_obj->set_value($dst_row, $dst_path, $combined_value);
    }
    private function conv_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src0_path =$param["src_paths"][0];
        $src1_path =$param["src_paths"][1];
        $dst_path = $param["dst_path"];
        $src0_value = $src_obj->value($src_row, $src0_path);
        $src1_value = $src_obj->value($src_row, $src1_path);
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