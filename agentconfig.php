<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$filename = "agents.conf";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $contents = file($filename);
    $return_data = array();
    foreach ($contents as $line) {
        $separated = explode(' = ', $line);
        $return_data[$separated[0]] = trim($separated[1], $characters = "\n\r\t\v\x00\"");
    }

    // Check if the index header is present
    $index = isset($_SERVER['HTTP_INDEX']) ? $_SERVER['HTTP_INDEX'] : null;

    // If the index is provided and exists in the array, return only that value
    if ($index !== null && array_key_exists($index, $return_data)) {
        $return_data = $return_data[$index];
    }

    header("Content-Type: application/json");
    echo json_encode($return_data);
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $myfile = fopen($filename, "w") or die("Unable to open file!");
    $data = file_get_contents('php://input');
    $agents = json_decode($data, true);

    foreach ($agents as $i => $agent) {
        fwrite($myfile, $i . ' = ' . $agent . PHP_EOL);
    }

    fclose($myfile);
    echo json_encode(array());
}
?>