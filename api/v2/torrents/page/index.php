<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */

header('Content-Type: application/json');

if (!empty($_GET["hash"])) {
    $debug = FALSE;
    if (!empty($_GET["debug"])) {
        $debug = TRUE;
    }
    $hashes = urldecode($_GET["hash"]);
    require_once("PageHandler.php");
    $api = new PageHandler($hashes, $debug);
    $api->getPage();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a hash."}';
    die($error);
}

