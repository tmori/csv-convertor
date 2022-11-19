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
    public function insert($line)
    {
        #print(var_dump($line));
        array_push($this->lines, $line);
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