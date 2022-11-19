<?php

function load_json($filepath)
{
    $json_str = file_get_contents($filepath);
    if ($json_str == false) {
        throw new Exception('ERROR: can open file: ' . $filepath);
    }
    $json_data = mb_convert_encoding($json_str, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
    #print("json_data=" . $json_data);
    
    $json_array = json_decode($json_data,true);
    return $json_array;    
}

?>