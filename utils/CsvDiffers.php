<?php

Class CsvDiffers
{
    private $is_debug;
    function __construct($is_debug)
    {
        $this->is_debug = $is_debug;
    }
    public function do_diff($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $conv_type = $param["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            return $this->conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "combine1") == 0) {
            return $this->conv_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "combine2") == 0) {
            return $this->conv_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "stroff") == 0) {
            return $this->conv_stroff($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
    private function do_check($src_row, $dst_row, $src_value, $dst_value)
    {
        if (strcmp($src_value, $dst_value) != 0) {
            printf("DIFF: src_line=%d dst_line=%d src_value=%s dst_value=%s\n",
                $src_row, $dst_row, $src_value, $dst_value);
            return 1;
        }
        else {
            if ($this->is_debug) {
                printf("PASSED: src_line=%d dst_line=%d src_value=%s dst_value=%s\n",
                $src_row, $dst_row, $src_value, $dst_value);
            }
            return 0;
        }
    }

    private function conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];

        $src_value = $src_obj->value($src_row, $src_path);
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        return $this->do_check($src_row, $dst_row, $src_value, $dst_value);
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
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        return $this->do_check($src_row, $dst_row, $combined_value, $dst_value);
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
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        return $this->do_check($src_row, $dst_row, $combined_value, $dst_value);
    }
    private function conv_stroff($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_path);
        if (is_null($src_value)) {
            printf("WARNING: not found src_row=%d src_path=%s\n", $src_row, $src_path);
            return 0;
        }

        $combined_value = "";
        foreach ($param["src_offs"] as $off) {
            if ($off < strlen($src_value)) {
                $combined_value = $combined_value . $src_value[$off];
            }
            else {
                $combined_value = "";
                break;
            }
        }
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        if ($combined_value === "") {
            if (is_null($dst_value)) {
                return 0;
            }
        }        
        if (is_null($dst_value)) {
            printf("ERROR: not found src_row=%d src_path=%s dst_row=%d dst_path\n", 
                $src_row, $src_path, $dst_row, $dst_path);
            return 1;
        }
        return $this->do_check($src_row, $dst_row, $combined_value, $dst_value);
    }

}

?>