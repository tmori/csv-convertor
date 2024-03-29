<?php

Class CsvConvertor
{
    private $serial_id;
    function __construct()
    {
        $serial_ivalue = getenv("SERIAL_ID_INITIAL_VALUE", $local_only = true);
        if (($serial_ivalue === false) || is_numeric($serial_ivalue)) {
            $this->serial_id = (int)$serial_ivalue;
        }
        else {
            #printf("SERIAL_ID_INITIAL_VALUE is not set\n");
            $this->serial_id = 0;
        }
        #printf("initial value=%d\n", $this->serial_id);
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
        else if (strcmp($conv_type, "cond_combine1") == 0) {
            $this->conv_cond_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "cond_combine2") == 0) {
            $this->conv_cond_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "combine2") == 0) {
            $this->conv_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "stroff") == 0) {
            $this->conv_stroff($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else if (strcmp($conv_type, "substr") == 0) {
            $this->conv_substr($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
    private function conv_fixed($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        if (isset($param["name"])) {
            $env_param_name = $param["name"]."_fixed_value";
            $fixed_value = getenv($env_param_name, $local_only = true);
            if (($fixed_value === false) || empty($fixed_value)) {
                $fixed_value = $param["value"];
            }
        }
        else {
            $fixed_value = $param["value"];
        }
        $dst_path = $param["dst_path"];

        $dst_obj->set_value($dst_row, $dst_path, $fixed_value);
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
            if ($dst_value && is_numeric($dst_value)) {
                $this->serial_id = $dst_value + 1;
                #printf("next_id=%d\n",  $this->serial_id );
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
        #printf("src_row=%d src_path=%s src_value=%s\n", $src_row, $src_path, $src_value);
        #printf("dst_row=%d dst_value=%s\n", $dst_row, $dst_obj->value($dst_row, $dst_path));
        $dst_obj->set_value($dst_row, $dst_path, $src_value);
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
    private function conv_cond_combine1($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_exclude_values = $param["src_exclude_values"];
        $src_exclude_path = $param["src_exclude_path"];

        $src_exclude_value = $src_obj->value($src_row, $src_exclude_path);
        foreach ($src_exclude_values as $exclude_value) {
            if (strpos($src_exclude_value, $exclude_value) !== false) {
                // nothing to do
                return;
            }
        }

        $src_cond_path =$param["src_cond_path"];
        $src_combine_path =$param["src_combine_path"];
        $src_cond_values = $param["src_cond_values"];
        $dst_path = $param["dst_path"];
        $src_cond_value = $src_obj->value($src_row, $src_cond_path);
        $src_value = $src_obj->value($src_row, $src_combine_path);
        foreach ($src_cond_values as $cond_value) {
            if (strpos($src_cond_value, $cond_value) !== false) {
                $combined_value = sprintf(
                    $param["src_combine_format"], 
                    $src_value
                );
                $dst_obj->set_value($dst_row, $dst_path, $combined_value);
                break;
            }
        }
    }
    private function conv_cond_combine2($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_cond_path =$param["src_cond_path"];
        $src0_combine_path =$param["src_combine_paths"][0];
        $src1_combine_path =$param["src_combine_paths"][1];

        $src_cond_values = $param["src_cond_values"];
        $dst_path = $param["dst_path"];
        $src_cond_value = $src_obj->value($src_row, $src_cond_path);
        $src0_value = $src_obj->value($src_row, $src0_combine_path);
        $src1_value = $src_obj->value($src_row, $src1_combine_path);

        $cond_check_result = false;
        foreach ($src_cond_values as $cond_value) {
            if (strpos($src_cond_value, $cond_value) !== false) {
                $cond_check_result = true;
                break;
            }
        }
        if ($cond_check_result) {
            $combined_value = sprintf(
                $param["src_combine_format_true"], 
                $src0_value, $src1_value
            );    
        }
        else {
            $combined_value = sprintf(
                $param["src_combine_format_false"], 
                $src0_value, $src1_value
            );
        }
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
    private function conv_stroff($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_path);

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
        $dst_obj->set_value($dst_row, $dst_path, $combined_value);
    }
    private function conv_substr($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];
        $src_value = $src_obj->value($src_row, $src_path);

        $next_value = substr($src_value, (int)$param["off"], (int)$param["size"]);;
        $dst_obj->set_value($dst_row, $dst_path, $next_value);
    }
}

?>