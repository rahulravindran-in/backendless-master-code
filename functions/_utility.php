<?php

function _genericApiToMap($url){
    $api_call = file_get_contents($url);
    $map = json_decode($api_call, true);
    return $map;
}

function _webQueryToResultMap ($url){
    $api_call = file_get_contents($url);
    $map = json_decode($api_call, true);
    $output = $map["response"];
    return $output;
}

function _concatenateMaps(){
    $concatenatedArray = array();
    $arguments = func_get_args();
    $mapNames = array();
    $maps = array();
    foreach ($arguments as $value){
        if(is_string($value)){
            array_push($mapNames, $value);
        }else if (is_array($value)){
            array_push($maps, $value);
        }
    }

    if(sizeof($mapNames) == sizeof($maps)){
        for ($i = 0; $i < sizeof($mapNames); $i++) {
            $concatenatedArray[$mapNames[$i]] = $maps[$i];
        }
    }else {
        return "Fatal Error. Your invocation of '_concatenateArray' has insufficient parameters.";
    }

    return json_encode($concatenatedArray);
}

function _sha256Hash($variable){
    return hash('sha256', $variable);
}

?>
