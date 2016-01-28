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

if (!empty($_GET["hash"])) {

    $hash = urldecode($_GET["hash"]);
    require_once("EpisodeHandler.php");
    $api = new EpisodeHandler($hash);
    $api->getShowInfo();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter hash"}';
    die($error);
}

