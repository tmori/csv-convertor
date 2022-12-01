<?php

Class CsvFileIo
{
    private $filepath;
    private $lines = array();
    private $colnum;
    private $map_pkeys = array();
    private $pkey_columns = NULL;

    function __construct($filepath)
    {
        $this->filepath = $filepath;
        $this->load();
    }

    private function load()
    {
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
            if ($value) {
                $pkey = $pkey . ":" . strval($value);
            }
            else {
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
            if ($value) {
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
        $this->pkey_columns = $pkey_columns;
        $start_line_int = (int)$start_line;
        $num = $this->linenum();
        for ($i = $start_line_int; $i < $num; $i++) {
            $mykey = $this->get_pkeys($i, $pkey_columns);
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
    public function get_value_by_pkey_with_cache($pkey)
    {
        if (isset($this->map_pkeys[$pkey])) {
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

    public function validate_pkeys($pkey_columns)
    {
        $num = $this->linenum();
        for ($i = 0; $i < $num; $i++) {
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            for ($j = 0; $j < $num; $j++) {
                if ($j == $i) {
                    continue;
                }
                $pkey2 = $this->get_pkeys($j, $pkey_columns);
                if (strcmp($pkey1, $pkey2) == 0) {
                    throw new Exception('ERROR: Invalid table same pkey is found in row ' . strval($i) . ' and ' . strval($j) . ' pkey=' . $pkey1);
                }
            }
        }
        return true;
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