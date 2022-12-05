<?php

Class CsvRelation
{
    private $relation_definitions;
    private $map_ids = array();
    private $objs;
    private $root_name = NULL;

    function __construct($relation_definitions, $objs)
    {
        $this->objs = $objs;
        $this->relation_definitions = $relation_definitions;
        foreach ($relation_definitions as $relation) {
            $key = $relation["parent"] . "/" . $relation["child"];
            $value = [
                "p"=> $relation["parent_colnames"]
            ];
            $this->map_ids[$key] = $value;
        }
    }
    public function set_root($root_name)
    {
        $this->root_name = $root_name;
    }
    public function row_by_root($path, $key)
    {
        $path_array = explode('/', $path);
        $root = $path_array[0];
        $name = $path_array[1];
        $row = $this->objs[$root]->get_value_by_pkey_with_cache($key);
        return $row;
    }

    private function row($parent_row, $path)
    {
        $path_array = explode('/', $path);
        $num = count($path_array);
        $name = $path_array[$num - 1];
        if ($num == 2) {
            return $parent_row;
        }
        $last_inx = $num - 2;
        for ($i = 1; $i < $num - 1; $i++) {
            $parent = $path_array[$i - 1];
            $child = $path_array[$i];
            $key = $parent . "/" . $child;
            $columns = $this->objs[$parent]->get_colinx_array($this->map_ids[$key]["p"]);
            $parent_value = $this->objs[$parent]->get_pkeys($parent_row, $columns);
            $child_row = $this->objs[$child]->get_value_by_pkey_with_cache($parent_value);
            if ($i == $last_inx) {
                return $child_row;
            }
            else {
                $paretn_row = $child_row;
            }
        }
        return null;
    }
    public function last_value_by_root($path, $minus_off)
    {
        $path_array = explode('/', $path);
        $root = $path_array[0];
        $colname = $path_array[1];
        $start_line = $this->objs[$root]->start_line();
        $index = $this->objs[$root]->colinx($colname);
        $last_off = $this->objs[$root]->linenum() - 1;
        #printf("last_off=%d\n", $last_off);
        $off = $last_off - $minus_off;
        $value = $this->objs[$root]->value($off, $index);
        return $value;
    }
    public function value($parent_row, $path)
    {
        $tmp = explode('/', $path);
        $target = $tmp[count($tmp) - 2];
        $name = $tmp[count($tmp) - 1];
        $row = $this->row($parent_row, $path);
        if ($row) {
            $value = $this->objs[$target]->value($row, $this->objs[$target]->colinx($name));
            #printf("%s.%s=%s\n", $path, $name, $value);
            return $value;
        }
        else {
            #printf("ERROR: not found path=%s\n", $path);
        }
        return NULL;
    }
    public function pkey($parent_row, $path)
    {
        $tmp = explode('/', $path);
        $target = $tmp[count($tmp) - 2];
        $name = $tmp[count($tmp) - 1];
        $row = $this->row($parent_row, $path);
        if ($row) {
            $pkey = $this->objs[$target]->get_pkeys($row, [ $this->objs[$target]->colinx($name) ]);
            return $pkey;
        }
        else {
            printf("ERROR: not found path=%s\n", $path);
        }
        return NULL;
    }

    public function set_value($parent_row, $path, $value)
    {
        $tmp = explode('/', $path);
        $target = $tmp[count($tmp) - 2];
        $name = $tmp[count($tmp) - 1];
        $row = $this->row($parent_row, $path);
        if ($row) {
            #printf("%s(%s)=%s target=%s\n", $path, $name, $value, $target);
            $this->objs[$target]->set_value($row, $this->objs[$target]->colinx($name), $value);
            return $value;
        }
        else {
            printf("Not Found!! parent_row=%d %s\n", $parent_row, $path);
        }
        return NULL;
    }

    private function get_childs($parent)
    {
        $childs = array();
        foreach ($this->relation_definitions as $relation) {
            if (strcmp($relation["parent"], $parent) == 0) {
                array_push($childs, $relation["child"]);
            }
        }
        return $childs;
    }

    private function get_relation_objs($cache_objs, $parent, $row)
    {
        #printf("get_relation_objs(): parent=%s\n", $parent);
        $cache_objs[$parent] = [
            "row" => $row,
            "obj" => $this->objs[$parent]
        ];
        $childs = $this->get_childs($parent);
        if (is_null($childs)) {
            return $cache_objs;
        }
        $pkey_columns = $this->objs[$parent]->pkey_columns();
        $pkeys = $this->objs[$parent]->get_pkeys($row, $pkey_columns);
        foreach ($childs as $child) {
            $key = $parent . "/" . $child;
            $columns = $this->objs[$parent]->get_colinx_array($this->map_ids[$key]["p"]);
            $parent_value = $this->objs[$parent]->get_pkeys($row, $columns);
            $child_row = $this->objs[$child]->get_value_by_pkey_with_cache($parent_value);
            $this->get_relation_objs($cache_objs, $child, $child_row);
        }
    }

    public function delete_cache_line($row)
    {
        $cache_objs = array();
        #printf("delete_cache_line(): root_name=%s\n", $this->root_name);
        $objs = $this->get_relation_objs($cache_objs, $this->root_name, $row);
        foreach ($cache_objs as $obj) {
            $obj["obj"]->delete_cache_line($obj["row"]);
        }
    }
    public function set_cache_line($row)
    {
        $cache_objs = array();
        $objs = $this->get_relation_objs($cache_objs, $this->root_name, $row);
        foreach ($cache_objs as $obj) {
            $obj["obj"]->set_cache_line($obj["row"]);
        }
    }

    private function newline($src_csv_obj, $dst_csv_obj, $src_row, $src_pkeys, $dst_pkeys)
    {
        $p_inx = 0;
        $line = $dst_csv_obj->get_empty_line();
        foreach ($dst_pkeys as $dst_pkey) {
            $v = $src_csv_obj->value($src_row, $src_pkeys[$p_inx]);
            $line[$dst_pkey] = $v;
            $p_inx++;
        }
        return $line;
    }
    private function create_newline_with_pkey_parent($parent, &$dst_rows, $src_csv_obj, $src_row)
    {
        $src_pkeys = $src_csv_obj->pkey_columns();
        $src_pkey = $src_csv_obj->get_pkeys($src_row, $src_pkeys);
        $dst_pkeys = $this->objs[$parent]->pkey_columns();
        $dst_csv_obj = $this->objs[$parent];
        $line = $this->newline($src_csv_obj, $this->objs[$parent], $src_row, $src_pkeys, $dst_pkeys);
        $dst_csv_obj->insert_with_cache($line, $dst_pkeys);
        $dst_row = $dst_csv_obj->get_value_by_pkey_with_cache($src_pkey);
        if (is_null($dst_row)) {
            throw new Exception('ERROR: ' . $parent . ' can not find pkey: ' . $src_pkey);
        }
        #printf("create: table=%s dst_row=%d\n", $parent, $dst_row);

        $dst_rows[$parent] = $dst_row;

        $childs = $this->get_childs($parent);
        if (is_null($childs)) {
            return $dst_rows;
        }
        foreach ($childs as $child) {
            $this->create_newline_with_pkey_parent(
                $child, 
                $dst_rows, 
                $this->objs[$parent], 
                $dst_row);
        }
        return $dst_row;
    }
    public function create_newline_with_pkey($src_csv_obj, $src_row)
    {
        $dst_rows = array();
        $this->create_newline_with_pkey_parent(
            $this->root_name, 
            $dst_rows, 
            $src_csv_obj, 
            $src_row);
        #printf("dst_rows count=%d\n", count($dst_rows));
        #foreach ($dst_rows as $k => $v) {
        #    printf("k=%s v=%s\n", $k, $v);
        #}
        return $dst_rows[$this->root_name];
    }

}

?>