<?php
require('utils/json_loader.php');

Class DataValidator
{
    private $validation_spec_file;
    private $validation_spec = array();
    private $csv_obj;
    private $success = true;

    function __construct($validation_spec_file, $csv_obj)
    {
        $this->csv_obj = $csv_obj;
        $this->validation_spec_file = $validation_spec_file;
        $this->validation_spec = load_json($validation_spec_file);
    }
    private function print_error($row, $colname, $value, $reason, $rule)
    {
        printf("FAILED, line number: %4d, colname: %s, value: %s, reason: %s\n", $row + 1, $colname, $value, $reason);
        #print_r($rule[$colname]);
    }

    private function validate_integer($row, $colname, $value, $rule): bool
    {
        if (preg_match('/^\d+$/', $value) == false) {
            $this->print_error($row, $colname, $value, "data type is not integer", $rule);
            return false;
        }
        $value_int = (int)$value;
        if ($value_int < (int)$rule[$colname]['min']) {
            $this->print_error($row, $colname, $value, "value less min: ". $rule[$colname]['min'], $rule);
            return false;
        }
        if ($value_int > (int)$rule[$colname]['max']) {
            $this->print_error($row, $colname, $value, "value over max: " . $rule[$colname]['max'], $rule);
            return false;
        }
        return true;
    }
    private function validate_string($row, $colname, $value, $rule): bool
    {
        if (mb_strlen($value) < (int)$rule[$colname]['min']) {
            $this->print_error($row, $colname, $value, "string length less min: ". $rule[$colname]['min'], $rule);
            return false;
        }
        if (mb_strlen($value) > (int)$rule[$colname]['max']) {
            $this->print_error($row, $colname, $value, "string length over max:" . $rule[$colname]['max'], $rule);
            return false;
        }
        if (preg_match($rule[$colname]['reg'], $value) == false) {
            $this->print_error($row, $colname, $value, "invalid string(valid regex:". $rule[$colname]['reg'] .")", $rule);
            return false;
        }
        return true;
    }    
    private function validate_null($row, $colname, $value, $rule): bool
    {
        if ($rule[$colname]['NULL'] == false) {
            if (isset($value)) {
                if (strlen($value) == 0) {
                    $this->print_error($row, $colname, $value, "data is empty", $rule);
                    return false;
                }
            }
            else {
                $this->print_error($row, $colname, $value, "data is null", $rule);
                return false;
            }
        }
        return true;
    }
    private function validate_one($row, $colname, $value, $rule): bool
    {
        $coltype = $rule[$colname]['type'];
        $ret = $this->validate_null($row, $colname, $value, $rule);
        if ($ret == false) {
            return false;
        }
        switch ($coltype) {
            case "integer":
                return $this->validate_integer($row, $colname, $value, $rule);
            case "string":
                return $this->validate_string($row, $colname, $value, $rule);
            default:
                printf("ERROR: INTERNAL ERROR Invalid Rule:");
                print_r($rule[$colname]);
                return false;
        }
        return true;
    }

    public function validate_data($row, $col)
    {
        foreach ($this->validation_spec["validation_rules"] as $rule) {
            $colname = key($rule);
            if ($this->csv_obj->colinx($colname) == $col) {
                $value = $this->csv_obj->value($row, $col);
                $ret = $this->validate_one($row, $colname, $value, $rule);
                if ($ret == false) {
                    $this->success = false;
                }
            }
        }
    }

    public function validate_record($row)
    {
        for ($col = 0; $col < $this->csv_obj->colnum(); $col++) {
            $this->validate_data($row, $col);
        }
    }

    public function validate(): bool
    {
        $this->success = true;
        $start_line = $this->validation_spec['start_line'];
        $linenum = $this->csv_obj->linenum();
        for ($row = $start_line; $row < $linenum; $row++) {
            $this->validate_record($row);
        }
        return $this->success;
    }

}

?>