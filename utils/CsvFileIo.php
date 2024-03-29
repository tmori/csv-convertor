<?php

Class CsvFileIo
{
    private $filepath;
    private $lines = array();
    private $colnum;
    private $map_pkeys = array();
    private $map_pkeys_for_dup = array();
    private $pkey_columns = NULL;
    private $start_line = 1;
    
    function __construct($filepath)
    {
        $this->filepath = $filepath;
        $this->load();
    }
    function set_start_line($start_line)
    {
        $this->start_line = $start_line;
    }
    function start_line()
    {
        return $this->start_line;
    }
    public function set_cols($cols)
    {
        $this->colnum = count($cols);
        array_push($this->lines, $cols);
    }
    private function load()
    {
        if (is_null($this->filepath)) {
            return;
        }
        $handle = fopen($this->filepath, "r");
        if ($handle == false) {
            throw new Exception('ERROR: can not find file: ' . $this->filepath);
        }
        while($line = fgetcsv($handle)) {
            #print(var_dump($line));
            array_push($this->lines, $line);
        }
        fclose($handle);
        $this->colnum = count($this->lines[0]);
    }
    public function add_col($name)
    {
        for ($i = 0; $i < $this->colnum();  $i++) {
            if (strcmp($this->lines[0][$i], $name) == 0) {
                throw new Exception('ERROR: overlap colname=' . $name);
            }
        }
        array_push($this->lines[0], $name);
        $this->colnum++;
        $num = $this->linenum();
        for ($i = 1; $i < $num; $i++) {
            array_push($this->lines[$i], "");
        }
        return;
    }
    public function del_col($colinx)
    {
        if ($colinx >= $this->colnum) {
            throw new Exception('ERROR: colinx over colnum('. strval($this->colnum) . '): can not del colinx=' . strval($colinx));
        }
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            array_splice($this->lines[$i], $colinx, 1);
        }
        $this->colnum--;
        return;
    }
    public function linenum()
    {
        return count($this->lines);
    }
    public function colnum()
    {
        return $this->colnum;
    }
    public function colinx($name)
    {
        if (gettype($name) == "integer") {
            return $name;
        }
        for ($i = 0; $i < $this->colnum(); $i++) {
            $colname = $this->value(0, $i);
            #printf("colinx(): colname=%s name=%s\n", $colname, $name);
            if (strcmp($colname, $name) == 0) {
                return $i;
            }
        }
        throw new Exception('ERROR: not found colname=' . $name);
    }
    public function get_colinx_array($pkey_columns)
    {
        $ret = array();
        foreach ($pkey_columns as $pkey_col) {
            array_push($ret, $this->colinx($pkey_col));
        }
        return $ret;
    }
    public function get_colinx_array_error_skip($pkey_columns)
    {
        $ret = array();
        foreach ($pkey_columns as $pkey_col) {
            try {
                $inx = $this->colinx($pkey_col);
            }
            catch (Exception) {
                continue;
            }
            array_push($ret, $this->colinx($pkey_col));
        }
        return $ret;
    }
    public function line($row)
    {
        $row_int = (int)$row;
        if ($row_int >= $this->linenum()) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum()) . '<= row=' . strval($row));
        }
        return $this->lines[$row_int];
    }
    public function value($row, $index)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        #printf("value:filepath=%s row=%d linenum=%d\n", $this->filepath, $row, $this->linenum());
        if ($row_int >= $this->linenum()) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum()) . '<= row=' . strval($row));
        }
        if ($index_int >= $this->colnum()) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum()) . '<= col=' . strval($index));
        }
        return $this->lines[$row_int][$index_int];
    }
    public function last_value($start_line, $index)
    {
        $start_line_int = (int)$start_line;
        $row_int = $this->linenum() - 1;
        $index_int = (int)$index;
        if ($index_int >= $this->colnum()) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum()) . '<= col=' . strval($index));
        }
        if ($row_int < $start_line_int) {
            #printf("row_int=%d start_line_int=%d\n", $row_int, $start_line_int);
            return 0;
        }
        #printf("last_value=%d\n", $this->lines[$row_int][$index_int]);
        return $this->lines[$row_int][$index_int];
    }
    public function set_value($row, $index, $value)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        if ($row_int >= $this->linenum()) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum()) . '<= row=' . strval($row));
        }
        if ($index_int >= $this->colnum()) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum()) . '<= col=' . strval($index));
        }
        #if ($this->pkey_columns) {
        #    foreach ($this->pkey_columns as $pkey_col) {
        #        if ($index == $pkey_col) {
        #            throw new Exception('ERROR: can not change pkey data colinx=' . strval($index));
        #        }
        #    }    
        #}
        $this->lines[$row_int][$index_int] = $value;
        #printf("set_value[%d][%d]=%s\n", $row_int, $index_int, $value);
    }
    public function get_empty_line()
    {
        $line = array();
        for ($i = 0; $i < $this->colnum(); $i++) {
            array_push($line, "");
        }
        return $line;
    }
    public function copy($src)
    {
        $new_line = $this->get_empty_line();
        for ($i = 0; $i < $this->colnum(); $i++) {
            $new_line[$i] = $src[$i];
        }
        return $new_line;
    }
    public function isEqual($src, $dst)
    {
        for ($i = 0; $i < $this->colnum(); $i++) {
            if (strcmp($src[$i], $dst[$i]) != 0) {
                return false;
            }
        }
        return true;
    }
    public function isEqualWithExcludeCols($src, $dst, $col_inx_array)
    {
        for ($i = 0; $i < $this->colnum(); $i++) {
            if (in_array($i, $col_inx_array)) {
                continue;
            }
            if (strcmp($src[$i], $dst[$i]) != 0) {
                return false;
            }
        }
        return true;
    }
    public function insert($line)
    {
        #print(var_dump($line));
        $new_line = $this->copy($line);
        array_push($this->lines, $new_line);
    }
    public function update($row, $line)
    {
        if (count($line) != $this->colnum()) {
            throw new Exception('ERROR: update failed invalid colnum: ' . strval(count($line)));
        }
        $this->lines[$row] = $line;
    }
    public function insert_with_cache($line, $pkey_columns)
    {
        $this->insert($line);
        $pkey = $this->get_pkeys_by_line($line, $pkey_columns);
        $this->map_pkeys[$pkey] = $this->linenum() - 1;
    }

    public function dump($dump_filepath = "./dump.csv")
    {
        $fp = fopen($dump_filepath, "w");
        if ($fp == false) {
            throw new Exception('ERROR: can open file: ' . $dump_filepath);
        }
        foreach ($this->lines as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);        
    }
    public function splice_all($start_line)
    {
        $start_line_int = (int)$start_line;
        $linenum_int = $this->linenum() - $start_line_int;
        if ($start_line_int >= $this->linenum()) {
            return;
        }
        array_splice($this->lines, $start_line_int, $linenum_int);
    }
    public function splice($start_line, $linenum)
    {
        $start_line_int = (int)$start_line;
        $linenum_int = (int)$linenum;
        if ($start_line_int >= $this->linenum()) {
            return;
        }
        array_splice($this->lines, $start_line_int, $linenum_int);
    }
    public function get_pkeys($row, $pkey_columns)
    {
        $pkey = "";
        foreach ($pkey_columns as $pkey_col)
        {
            $value = $this->value($row, $pkey_col);
            if ($value != NULL) {
                $pkey = $pkey . ":" . strval($value);
            }
            else {
                #printf("NULL\n");
                return NULL;
            }
            #print($pkey . "\n");
        }
        return $pkey;
    }
    public function get_value_by_pkey($start_line, $pkey_columns, $pkey)
    {
        $start_line_int = (int)$start_line;
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $mykey = $this->get_pkeys($i, $pkey_columns);
            if (strcmp($pkey, $mykey) == 0) {
                return $i;
            }
        }
        #throw new Exception('ERROR: Not found pkey is found in row  pkey=' . $pkey);
        return NULL;
    }
    public function get_pkeys_by_line($line, $pkey_columns)
    {
        $pkey = "";
        foreach ($pkey_columns as $pkey_col)
        {
            $value = $line[$pkey_col];
            if ($value != NULL) {
                $pkey = $pkey . ":" . strval($value);
            }
            else {
                return NULL;
            }
            #print($pkey . "\n");
        }
        return $pkey;
    }
    public function create_cache($start_line, $pkey_columns)
    {
        #printf("pkey_comuns=%s\n", $pkey_columns[0]);
        $this->pkey_columns = $pkey_columns;
        $start_line_int = (int)$start_line;
        $this->set_start_line($start_line_int);
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $mykey = $this->get_pkeys($i, $pkey_columns);
            #printf("%s\n", $mykey);
            if (isset($this->map_pkeys[$mykey])) {
                throw new Exception('ERROR: Invalid table same pkey is found in row ' . strval($i) . ' and ' . strval($this->map_pkeys[$mykey]) . ' pkey=' . $mykey);
            }
            $this->map_pkeys[$mykey] = $i;
        }
    }
    public function delete_cache_line($row)
    {
        $mykey = $this->get_pkeys($row, $this->pkey_columns);
        $this->map_pkeys[$mykey] = NULL;
    }
    public function set_cache_line($row)
    {
        $mykey = $this->get_pkeys($row, $this->pkey_columns);
        $this->map_pkeys[$mykey] = $row;
    }
    public function pkey_columns()
    {
        return $this->pkey_columns;
    }
    public function get_value_by_pkey_with_cache($pkey)
    {
        if (isset($this->map_pkeys[$pkey]) && $this->map_pkeys[$pkey] != NULL) {
            return $this->map_pkeys[$pkey];
        }
        else {
            return NULL;
        }
    }
    public function diff($pkey_columns, $start_line, $new_csv_obj, $dump_dir)
    {
        $start_line_int = (int)$start_line;
        $this->dump($dump_dir . "/same.csv");
        $this->dump($dump_dir . "/create.csv");
        $this->dump($dump_dir . "/delete.csv");
        $this->dump($dump_dir . "/update-old.csv");
        $this->dump($dump_dir . "/update-new.csv");
        
        $same_csv_obj = new CsvFileIo($dump_dir . "/same.csv");
        $same_csv_obj->splice_all($start_line_int);
        $create_csv_obj = new CsvFileIo($dump_dir . "/create.csv");
        $create_csv_obj->splice_all($start_line_int);
        $delete_csv_obj = new CsvFileIo($dump_dir . "/delete.csv");
        $delete_csv_obj->splice_all($start_line_int);
        $update_old_csv_obj = new CsvFileIo($dump_dir . "/update-old.csv");
        $update_old_csv_obj->splice_all($start_line_int);
        $update_new_csv_obj = new CsvFileIo($dump_dir . "/update-new.csv");
        $update_new_csv_obj->splice_all($start_line_int);
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            $row = $new_csv_obj->get_value_by_pkey_with_cache($pkey1);
            if (is_null($row)) {
                //deleted
                $delete_csv_obj->insert($this->line($i));
            }
            else {
                if ($this->isEqual($this->line($i), $new_csv_obj->line($row))) {
                    $same_csv_obj->insert($this->line($i));
                }
                else {
                    $update_old_csv_obj->insert($this->line($i));
                    $update_new_csv_obj->insert($new_csv_obj->line($row));
                }
            }
        }
        $new_num = $new_csv_obj->linenum();
        for ($i = $start_line_int; $i < $new_num; $i++) {
            $is_found = false;
            $pkey1 = $new_csv_obj->get_pkeys($i, $pkey_columns);
            $row = $this->get_value_by_pkey_with_cache($pkey1);
            if (is_null($row)) {
                //created
                $create_csv_obj->insert($new_csv_obj->line($i));
            }
            else {
                //nothing to do
            }
        }
        $same_csv_obj->dump($dump_dir . "/same.csv");
        $create_csv_obj->dump($dump_dir . "/create.csv");
        $delete_csv_obj->dump($dump_dir . "/delete.csv");
        $update_old_csv_obj->dump($dump_dir . "/update-old.csv");
        $update_new_csv_obj->dump($dump_dir . "/update-new.csv");

        return true;
    }
    public function diff_with_exclude($pkey_columns, $start_line, $new_csv_obj, $dump_dir, $exclude_cols)
    {
        $col_inx_array = $this->get_colinx_array_error_skip($exclude_cols);
        $start_line_int = (int)$start_line;
        $this->dump($dump_dir . "/same.csv");
        $this->dump($dump_dir . "/create.csv");
        $this->dump($dump_dir . "/delete.csv");
        $this->dump($dump_dir . "/update-old.csv");
        $this->dump($dump_dir . "/update-new.csv");
        
        $same_csv_obj = new CsvFileIo($dump_dir . "/same.csv");
        $same_csv_obj->splice_all($start_line_int);
        $create_csv_obj = new CsvFileIo($dump_dir . "/create.csv");
        $create_csv_obj->splice_all($start_line_int);
        $delete_csv_obj = new CsvFileIo($dump_dir . "/delete.csv");
        $delete_csv_obj->splice_all($start_line_int);
        $update_old_csv_obj = new CsvFileIo($dump_dir . "/update-old.csv");
        $update_old_csv_obj->splice_all($start_line_int);
        $update_new_csv_obj = new CsvFileIo($dump_dir . "/update-new.csv");
        $update_new_csv_obj->splice_all($start_line_int);
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            $row = $new_csv_obj->get_value_by_pkey_with_cache($pkey1);
            if (is_null($row)) {
                //deleted
                $delete_csv_obj->insert($this->line($i));
            }
            else {
                if ($this->isEqualWithExcludeCols($this->line($i), $new_csv_obj->line($row), $col_inx_array)) {
                    $same_csv_obj->insert($this->line($i));
                }
                else {
                    $update_old_csv_obj->insert($this->line($i));
                    $update_new_csv_obj->insert($new_csv_obj->line($row));
                }
            }
        }
        $new_num = $new_csv_obj->linenum();
        for ($i = $start_line_int; $i < $new_num; $i++) {
            $is_found = false;
            $pkey1 = $new_csv_obj->get_pkeys($i, $pkey_columns);
            $row = $this->get_value_by_pkey_with_cache($pkey1);
            if (is_null($row)) {
                //created
                $create_csv_obj->insert($new_csv_obj->line($i));
            }
            else {
                //nothing to do
            }
        }
        $same_csv_obj->dump($dump_dir . "/same.csv");
        $create_csv_obj->dump($dump_dir . "/create.csv");
        $delete_csv_obj->dump($dump_dir . "/delete.csv");
        $update_old_csv_obj->dump($dump_dir . "/update-old.csv");
        $update_new_csv_obj->dump($dump_dir . "/update-new.csv");

        return true;
    }

    public function validate_pkeys($pkey_columns, &$error_list = null, $skip_empty = false)
    {
        $success = true;
        $num = $this->linenum();
        $start_line_int = (int)$this->start_line;
        for ($i = $start_line_int; $i < $num; $i++) {
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            if (($skip_empty == true) && (isset($pkey1) == false)) {
                continue;
            }
            if (count($this->map_pkeys_for_dup[$pkey1]) >= 2) {
                if (isset($error_list)) {
                    foreach ($this->map_pkeys_for_dup[$pkey1] as $j) {
                        if ($j === $i) {
                            continue;
                        }
                        $reason = "FILE: " . $this->filepath . " has duplicate data";
                        $error_msg = sprintf("FAILED, line number: (%4d , %4d) , col index: %s, reason: %s", 
                            $i + +$this->start_line, $j + $this->start_line, implode($pkey_columns), $reason);
                        array_push($error_list, $error_msg);    
                    }
                }
                $success = false;
            }
        }
        return $success;
    }
    public function create_cache_for_dup($start_line, $pkey_columns)
    {
        #printf("pkey_comuns=%s\n", $pkey_columns[0]);
        $this->pkey_columns = $pkey_columns;
        $start_line_int = (int)$start_line;
        $this->set_start_line($start_line_int);
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $mykey = $this->get_pkeys($i, $pkey_columns);
            #printf("%s\n", $mykey);
            if (isset($this->map_pkeys_for_dup[$mykey])) {
                array_push($this->map_pkeys_for_dup[$mykey], $i);
            }
            else {
                $tmp_array = array();
                array_push($tmp_array, $i);
                $this->map_pkeys_for_dup[$mykey] = $tmp_array;
            }
        }
    }

    public function get_row($start_line, $keyword, $index)
    {
        $index_int = (int)$index;
        if ($index_int >= $this->colnum()) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum()) . '<= col=' . strval($index));
        }
        $num = $this->linenum();
        for ($i = $start_line; $i < $num; $i++) {
            if (strcmp($keyword, $this->value($i, $index)) == 0) {
                return $i;
            }
        }
        throw new Exception('ERROR: not found keyword=' . $keyword . " index=" . strval($index) . "\n");
    }
    public function is_empty($row)
    {
        for ($col = 0; $col < $this->colnum(); $col++)
        {
            $value = $this->value($row, $col);
            //https://qiita.com/hirossyi73/items/6e6b9b3ff155a8b05075
            if ($value) {
                return false;
            }
        }
        return true;
    }
    public function shrink()
    {
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            if ($this->is_empty($i)) {
                $this->splice_all($i);
                break;
            }
        }
    }
    public function add_double_quote()
    {
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            for ($j = 0; $j < $this->colnum(); $j++) {
                $value = $this->value($i, $j);
                $dvalue = sprintf('%s', $value);
                $this->set_value($i, $j, $dvalue);
            }
        }
    }
    public function to_json()
    {
        $json_array = array();
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            $record_json_array = array();
            for ($j = 0; $j < $this->colnum(); $j++) {
                $colname = $this->value(0, $j);
                $value = $this->value($i, $j);
                $record_json_array = array_merge($record_json_array, [$colname => $value]);
            }
            array_push($json_array, $record_json_array);
        }
        return json_encode($json_array);
    }

    public function dump_with_double_quote($dump_filepath = "./dump.csv")
    {
        $fp = fopen($dump_filepath, "w");
        if ($fp == false) {
            throw new Exception('ERROR: can open file: ' . $dump_filepath);
        }
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            $line = '';
            for ($j = 0; $j < $this->colnum(); $j++) {
                $value = $this->value($i, $j);
                if ($j != ($this->colnum() -1)) {
                    $line .= '"' . $value . '"' . ",";
                }
                else {
                    $line .= '"' . $value .'"' . "\n";
                }
            }
            fwrite($fp, $line);
        }
        fclose($fp);        
    }
}




?>