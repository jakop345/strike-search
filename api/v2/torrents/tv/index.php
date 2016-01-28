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

if (!empty($_GET["title"]) ||!empty($_GET["imdb"])) {
	$imdbid = "";
if (!empty($_GET["imdb"])) {
	
	$imdbid = urldecode($_GET["imdb"]);
}
    $title = urldecode($_GET["title"]);
    require_once("TVHandler.php");
    $api = new TVHandler($title, $imdbid);
    $api->getShowInfo();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a show title"}';
    die($error);
}

