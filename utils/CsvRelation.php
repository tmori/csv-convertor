<?php

Class CsvRelation
{
    private $relation_definitions;
    private $map_ids = array();
    function __construct($relation_definitions)
    {
        $this->relation_definition = $relation_definitions;
        foreach ($relation_definitions as $relation) {
            $key = $relation["parent"] . "." . $relation["child"];
            $value = [
                "p"=> $relation["parent_colname"],
                "c"=> $relation["child_colname"]
            ];
            $this->map_ids[$key] = $value;
        }
    }

    function get_value($parent_row, $objs, $path, $name)
    {
        $path_array = explode('.', $path);
        $parent = $path_array[0];
        $num = count($path_array);
        $last_inx = $num - 1;
        for ($i = 1; $i < $num; $i++) {
            $parent = $path_array[$i - 1];
            $child = $path_array[$i];
            #printf("parent=%s\n", $parent);
            $key = $parent . "." . $child;
            #printf("tmp key=%s\n", $this->map_ids[$key]["p"]);
            $colinx = $objs[$parent]->colinx($this->map_ids[$key]["p"]);
            $parent_value = $objs[$parent]->get_pkeys($parent_row, [ $colinx ]);
            #printf("parent_value=%s\n", $parent_value);
            #printf("child=%s\n", $child);
            $colinx = $objs[$child]->colinx($this->map_ids[$key]["c"]);
            #TODO
            $child_row = $objs[$child]->get_value_by_pkey(1, [ $colinx ], $parent_value);
            if ($i == $last_inx) {
                $child_value = $objs[$child]->value($child_row, $objs[$child]->colinx($name));
                printf("%s.%s=%s\n", $path, $name, $child_value);
            }
        }
        return NULL;
    }    
}

?>