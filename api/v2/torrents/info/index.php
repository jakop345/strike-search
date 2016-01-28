<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');

if (!empty($_GET["hashes"])) {
    $debug = FALSE;
    if (!empty($_GET["debug"])) {
        $debug = TRUE;
    }
    $hashes = urldecode($_GET["hashes"]);
    require_once("InfoHandler.php");
    $api = new InfoHandler($hashes, $debug);
    $api->getHashInformation();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a hash."}';
    die($error);
}

