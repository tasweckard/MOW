<?php

session_start();

/*
if (empty($_SESSION['authorized']) || $_SESSION['authorized'] != '1') {
    $response = json_encode(['error' => ['id' => 100, 'message' => 'Not Logged In']]);
    header('Content-type: application/json');
    echo $response;
    exit(0);
}
*/

$task = empty($_REQUEST['t'])?'':$_REQUEST['t'];

if ($task == 'routes') {
    // get routes
    getRoutes();
}
elseif ($task == 'login') {
    $pin = empty($_REQUEST['pin'])?'':filter_var($_REQUEST['pin'], FILTER_SANITIZE_STRING);
    
    $pins = getPins();
    $values = $pins['values'];
    unset($_SESSION['route']);
    $result = array(0);
    foreach ($values as $route) {
        //echo $pin . " " . print_r($route,true);
        if ($pin == $route[1]) {
            $result = array($route[0]);
            $_SESSION['route'] = $route[0];
        }
    }
    sendData($result);
}
elseif ($task == 'route') {
    if (empty($_SESSION['route'])) {
        sendError(100,'No route specified');
    }
    getRoute();
}

function getRoutes() {    
    $stops = getData();
    $stops = $stops['values'];
    array_shift($stops);
    $routeData = array();
    foreach($stops as $stop) {
        if (!in_array($stop[7],$routeData)) {
            $routeData[] = $stop[7];
        }
    }
    sendData(array('values' => $routeData));
}

function getRoute() {
    $stops = getData();
    $stops = $stops['values'];
    $routeData = array();
    foreach($stops as $stop) {
        if ($stop[7] == $_SESSION['route']) {
            $routeData[] = $stop;
        }
    }
    sendData(array('values' => $routeData));
}

function sendData($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    exit(0);
}

/**
 * Send error message back to client
 * @param string $id
 * @param string $message
 */
function sendError($id, $message) {
    $route = empty($_SESSION['route'])?'No Route':$_SESSION['route'];
    $response = json_encode(array(array('error' => 1, 'id' => $id, 'message' => $message, 'route' => $route)));
    header('Content-type: application/json');
    echo $response;
    exit(0);
}

/**
 * Responsible for getting all data from spreadsheet.
 * @return array
 */
function getData() {
    // removed for security
    $sheetKey = '';
    $sheetId  = '';
    
    $sheetApi = 'https://sheets.googleapis.com/v4/spreadsheets/' . $sheetId . '/values/Sheet1!A1:N89?key=' . $sheetKey;
    
    $session = curl_init($sheetApi);
    /*
    curl_setopt($session, CURLOPT_POST, true);
    curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
    */
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($session, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($session);
    curl_close($session);
    $results = json_decode($response, true);

    return $results;
}

/**
 * Responsible for getting a list of pins.  This function should make a post request, not a get request.
 */
function getPins() {
    $sheetKey = '';
    $sheetId  = '';
    
    $sheetApi = 'https://sheets.googleapis.com/v4/spreadsheets/' . $sheetId . '/values/Sheet1!A1:B5?key=' . $sheetKey;
    
    $session = curl_init($sheetApi);
    /*
     curl_setopt($session, CURLOPT_POST, true);
     curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
     curl_setopt($session, CURLOPT_HEADER, false);
     curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
     */
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($session, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($session);
    curl_close($session);
    $results = json_decode($response, true);
    
    return $results;
}


