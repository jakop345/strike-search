<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */
ini_set('max_execution_time', 5); // prints 10 (or whatever the old value was)
register_shutdown_function('shutdown');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');
function shutdown()
{
    
    if (connection_aborted()) {
        die();
    } else if (connection_status() == CONNECTION_TIMEOUT) {
        http_response_code(500);
        $error = '{"statuscode":500,"message":"This query took to long, maybe there was an internal error"}';
        die($error);
    } else {
        
    }
    
}


if (!empty($_GET["category"])) {
    $subCategory = "";
    if (!empty($_GET["subCategory"])) {
        $subCategory = urldecode($_GET["subCategory"]);
    }
    $category = urldecode($_GET["category"]);
    require_once("TopHandler.php");
    $api = new TopHandler($category, $subCategory);
    $api->fetchTop100();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a category"}';
    die($error);
}
