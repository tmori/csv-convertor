<?php

Class CsvDiffers
{
    private $serial_id;
    function __construct()
    {
    }
    public function do_diff($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $conv_type = $param["conv_type"];
        if (strcmp($conv_type, "normal") == 0) {
            return $this->conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row);
        }
        else {
            throw new Exception('ERROR: Not found conv_type=' . $conv_type);
        }
    }
    private function conv_normal($param, $src_obj, $src_row, $dst_obj, $dst_row)
    {
        $src_path =$param["src_path"];
        $dst_path = $param["dst_path"];

        $src_value = $src_obj->value($src_row, $src_path);
        $dst_value = $dst_obj->value($dst_row, $dst_path);
        if (strcmp($src_value, $dst_value) != 0) {
            printf("DIFF: src_line=%d dst_line=%d src_value=%s dst_value=%s\n",
                $src_row, $dst_row, $src_value, $dst_value);
            return 1;
        }
        else {
            return 0;
        }
    }

}

?>