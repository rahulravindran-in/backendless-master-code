<?php


function _genericApiToMap($url){
    $urlEncoded = urlencode($url);
    $api_call = file_get_contents($urlEncoded);
    $map = json_decode($api_call, true);
    return $map;
}

function _webQueryToResultMap ($url){
    // $urlEncoded = urlencode($url);
    $urlEncoded = $url;
    $api_call = file_get_contents($urlEncoded);
    $map = json_decode($api_call, true);
    $output = $map["response"];
    return $output;
}

function _apiCost($execution_time){
    $v = round($execution_time*BACKENDLESS_CONSTANT,3) . " " . CREDIT_NAME_PREFIX;
    return $v;
}

function _outputRestApi($status, $message, $execution_time, $response){
    $output = array(
        'response' => array(
            'status' => $status,
            'message' => $message,
            'api_cost' => _apiCost($execution_time),
            'result' => $response
        )
    );
    return $output;
}

function _arrayToJSON($array){
    return json_encode($array);
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

### Pipify Code --Starts
# $inputPipeAndFilterObject, $command, $args ...
function _pipify(){
    # Variable storing all function parameters.
    $arguments = func_get_args();
    # PipeAndFilterObject As Input
    $pipeAndFilterObject = NULL;
    # Example => "add", "update", "delete"
    $arrayOfValidCommandActions = array('add', 'delete', 'update', 'select');
    $commandAction = NULL;
    # Example => "input", "output", "code", "description", "status"
    $arrayOfValidVariableLocation = array('input', 'output', 'code', 'description', 'status');
    $commandVariableLocation = NULL;
    # example => "name", "age", "id"
    $arrayOfTheNameOfVariable = array();
    # example => "Rahul Ravindran", "18", "#279"
    $arrayOfValuesToBeOperatedWith = array();
    $count = 0;
    $isName = true;
    foreach ($arguments as $value){
        $count++;
        if($count == 1){
            # PipeAndFilterObject is the first argument.
            if(is_null($value)){
                # This is the init state
                $pipeAndFilterObject = array(
                    "input" => array(),
                    "output" => array(),
                    "code" => array(),
                    "description" => array(),
                    "status" => 0,
                );
            }else{
                $pipeAndFilterObject = $value;
            }
        }else if($count == 2){
            # commandAction_commandArrayVariableLocation is the second argument.
            $arrayOfCommandActionAndCommandArrayVariableLocation = explode("_", $value);
            # [0] => CommandAction
            # [1] => CommandArrayVariableLocation
            $commandAction = strtolower(
                $arrayOfCommandActionAndCommandArrayVariableLocation[0]
            );
            $commandVariableLocation = strtolower(
                $arrayOfCommandActionAndCommandArrayVariableLocation[1]
            );
            # validate weather the 2 keywords are valid
            if(!(
                in_array($commandAction, $arrayOfValidCommandActions) &&
                in_array($commandVariableLocation, $arrayOfValidVariableLocation))
            ){
                return "Invalid Command Action or Command Variable Location in your invocation of _pipify.";
            }
        }else {
            # Override if status
            if( $commandVariableLocation == "status"  && $commandAction != "delete"){
                $arrayOfTheNameOfVariable[] = "status";
                $arrayOfValuesToBeOperatedWith[] = $value;
                $isName = false;
            }
            # variables to be inserted/updated
            if($commandAction == "add" || $commandAction == "update"){
                if($isName){
                    $arrayOfTheNameOfVariable[] = $value;
                }else{
                    $arrayOfValuesToBeOperatedWith[] = $value;
                }
            }
            # variables to be deleted
            if(($commandAction == "delete" || $commandAction == "select") && $commandVariableLocation != "status"){
                $arrayOfTheNameOfVariable[] = $value;
            }
            $isName = !$isName;
        }
    }
    # Delete/Select Status is a 2 parameter invocation; thus to be kept outside the above loop.
    if(($commandAction == "delete" || $commandAction == "select") && $commandVariableLocation == "status"){
        $arrayOfTheNameOfVariable = array("status");
        $arrayOfValuesToBeOperatedWith = array("0");
    }
    # Validations
    $isSuccess = _pipify_areAllValidationsSuccessful(
        _pipify_isNameAndValueEven(
            $arrayOfTheNameOfVariable,
            $arrayOfValuesToBeOperatedWith,
            $commandAction,
            $commandVariableLocation
        ),
        _pipify_isOnlyOneArgumentForSelect(
            $arrayOfTheNameOfVariable,
            $arrayOfValuesToBeOperatedWith,
            $commandAction,
            $commandVariableLocation
        )
    );
    if(!$isSuccess){
        return "Invalid _pipify call";
    }
    # performing the actions
    for ($i = 0; $i < count($arrayOfTheNameOfVariable); $i++){
        $currentName = $arrayOfTheNameOfVariable[$i];
        $currentValue = $arrayOfValuesToBeOperatedWith[$i];
        $pipeAndFilterObject = _pipify_functionCommandAction($pipeAndFilterObject, $commandAction, $commandVariableLocation, $currentName, $currentValue);
    }
    # In reality it could return error message, if select statement => the value, or the pipeAndFilterObjectItself in other cases as intuitively known.
    return $pipeAndFilterObject;
}
function _pipify_areAllValidationsSuccessful(){
    $arguments = func_get_args();
    foreach ($arguments as $value){
        if(!$value){
            return false;
        }
    }
    return true;
}
function _pipify_isNameAndValueEven($arrayOfTheNameOfVariable, $arrayOfValuesToBeOperatedWith, $commandAction, $commandVariableLocation){
    # For delete both arrays not required
    if($commandAction == "delete" || $commandAction == "select" || $commandVariableLocation == "status"){
        return true;
    }
    return (count($arrayOfTheNameOfVariable) == count($arrayOfValuesToBeOperatedWith));
}
function _pipify_isOnlyOneArgumentForSelect($arrayOfTheNameOfVariable, $arrayOfValuesToBeOperatedWith, $commandAction, $commandVariableLocation){
    if(count($arrayOfTheNameOfVariable) > 1 && $commandAction == "select"){
        return false;
    }
    return true;
}
function _pipify_functionCommandAction($pipeAndFilterObject, $action, $commandVariableLocation, $name, $value){
    switch($action){
        case "add":
            return _pipify_functionCommandAction_add($pipeAndFilterObject, $name, $value, $commandVariableLocation);
         break;
        case "update":
            return _pipify_functionCommandAction_update($pipeAndFilterObject, $name, $value, $commandVariableLocation);
        break;
        case "delete":
            return _pipify_functionCommandAction_delete($pipeAndFilterObject, $name, $commandVariableLocation);
        break;
        case "select":
            return _pipify_functionCommandAction_select($pipeAndFilterObject, $name, $commandVariableLocation);
        break;
    }
    return "Invalid Command Action.";
}
function _pipify_functionCommandAction_add($pipeAndFilterObject, $name, $value, $commandVariableLocation){
    if($commandVariableLocation == "status"){
        $pipeAndFilterObject[$commandVariableLocation] = $value;
    }else{
        $pipeAndFilterObject[$commandVariableLocation][$name] = $value;
    }
    return $pipeAndFilterObject;
}
function _pipify_functionCommandAction_update($pipeAndFilterObject, $name, $value, $commandVariableLocation){
    // Same functionality As Add.
    return _pipify_functionCommandAction_add(
        $pipeAndFilterObject,
        $name,
        $value,
        $commandVariableLocation
    );
}
function _pipify_functionCommandAction_delete($pipeAndFilterObject, $name, $commandVariableLocation){
    if($commandVariableLocation == "status"){
        $pipeAndFilterObject[$commandVariableLocation] = 0;
    }else{
        unset($pipeAndFilterObject[$commandVariableLocation][$name]);
    }
    return $pipeAndFilterObject;
}
function _pipify_functionCommandAction_select($pipeAndFilterObject, $name, $commandVariableLocation){
    $dataToReturn = NULL;
    if($commandVariableLocation == "status"){
        $dataToReturn = $pipeAndFilterObject[$commandVariableLocation];
    }else{
        $dataToReturn = $pipeAndFilterObject[$commandVariableLocation][$name];
    }
    if(is_null($dataToReturn)){
        $dataToReturn = "Selected Parameter does not exist. Recheck for any typos.";
    }
    return $dataToReturn;
}
### Pipify Code --Ends

?>
