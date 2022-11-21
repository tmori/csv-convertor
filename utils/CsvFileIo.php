<?php

Class CsvFileIo
{
    private $filepath;
    private $lines = array();
    private $linenum;
    private $colnum;

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
        $this->linenum = 0;
        while($line = fgetcsv($handle)) {
            #print(var_dump($line));
            array_push($this->lines, $line);
            $this->linenum++;
        }
        fclose($handle);
        $this->colnum = count($this->lines[0]);
    }
    public function linenum()
    {
        return $this->linenum;
    }
    public function colnum()
    {
        return $this->colnum;
    }
    public function line($row)
    {
        $row_int = (int)$row;
        if ($row_int >= $this->linenum) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum) . '<= row=' . strval($row));
        }
        return $this->lines[$row_int];
    }
    public function value($row, $index)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        if ($row_int >= $this->linenum) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum) . '<= row=' . strval($row));
        }
        if ($index_int >= $this->colnum) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum) . '<= col=' . strval($index));
        }
        return $this->lines[$row_int][$index_int];
    }
    public function set_value($row, $index, $value)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        if ($row_int >= $this->linenum) {
            throw new Exception('ERROR: overflow linenum=' . strval($this->linenum) . '<= row=' . strval($row));
        }
        if ($index_int >= $this->colnum) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum) . '<= col=' . strval($index));
        }
        $this->lines[$row_int][$index_int] = $value;
    }
    public function get_empty_line()
    {
        $line = array();
        for ($i=0; $i<count($line); $i++) {
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
        $this->linenum++;
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
        if ($start_line_int >= $this->linenum) {
            return;
        }
        array_splice($this->lines, $start_line_int, $linenum_int);
        $this->linenum = $start_line_int;
    }
    public function get_pkeys($row, $pkey_columns)
    {
        $pkey = "";
        foreach ($pkey_columns as $pkey_col)
        {
            $pkey = $pkey . ":" . strval($this->value($row, $pkey_col));
            #print($pkey . "\n");
        }
        return $pkey;
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
        $same_csv_obj->splice_all(1);
        $create_csv_obj = new CsvFileIo($dump_dir . "/create.csv");
        $create_csv_obj->splice_all(1);
        $delete_csv_obj = new CsvFileIo($dump_dir . "/delete.csv");
        $delete_csv_obj->splice_all(1);
        $update_old_csv_obj = new CsvFileIo($dump_dir . "/update-old.csv");
        $update_old_csv_obj->splice_all(1);
        $update_new_csv_obj = new CsvFileIo($dump_dir . "/update-new.csv");
        $update_new_csv_obj->splice_all(1);
        
        for ($i = $start_line_int; $i < $this->linenum(); $i++) {
            $is_found = false;
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            for ($j = $start_line_int; $j < $new_csv_obj->linenum(); $j++) {
                $pkey2 = $new_csv_obj->get_pkeys($j, $pkey_columns);
                if (strcmp($pkey1, $pkey2) == 0) {
                    //update or same
                    if ($this->isEqual($this->line($i), $new_csv_obj->line($j))) {
                        $same_csv_obj->insert($this->line($i));
                    }
                    else {
                        $update_old_csv_obj->insert($this->line($i));
                        $update_new_csv_obj->insert($new_csv_obj->line($j));
                    }
                    $is_found = true;
                    break;
                }
            }
            if ($is_found == false) {
                //deleted
                $delete_csv_obj->insert($this->line($i));
            }
        }
        for ($i = $start_line_int; $i < $new_csv_obj->linenum(); $i++) {
            $is_found = false;
            $pkey1 = $new_csv_obj->get_pkeys($i, $pkey_columns);
            for ($j = $start_line_int; $j < $this->linenum(); $j++) {
                $pkey2 = $this->get_pkeys($j, $pkey_columns);
                if (strcmp($pkey1, $pkey2) == 0) {
                    $is_found = true;
                }
            }
            if ($is_found == false) {
                //created
                $create_csv_obj->insert($new_csv_obj->line($i));
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
        for ($i = 0; $i < $this->linenum(); $i++) {
            $pkey1 = $this->get_pkeys($i, $pkey_columns);
            for ($j = 0; $j < $this->linenum(); $j++) {
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
        if ($index_int >= $this->colnum) {
            throw new Exception('ERROR: overflow colnum=' . strval($this->colnum) . '<= col=' . strval($index));
        }
        for ($i = $start_line; $i < $this->linenum; $i++) {
            if (strcmp($keyword, $this->value($i, $index)) == 0) {
                return $i;
            }
        }
        throw new Exception('ERROR: not found keyword=' . $keyword . " index=" . strval($index) . "\n");
    }
}




?>