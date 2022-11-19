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
            return NULL;
        }
        return $this->lines[$row_int];
    }
    public function value($row, $index)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        if ($row_int >= $this->linenum) {
            return NULL;
        }
        if ($index_int >= $this->colnum) {
            return NULL;
        }
        return $this->lines[$row_int][$index_int];
    }
    public function set_value($row, $index, $value)
    {
        $row_int = (int)$row;
        $index_int = (int)$index;
        if ($row_int >= $this->linenum) {
            return NULL;
        }
        if ($index_int >= $this->colnum) {
            return NULL;
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
        foreach ($this->lines as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);        
    }
}




?>