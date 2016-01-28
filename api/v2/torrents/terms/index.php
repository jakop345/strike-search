<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */
header('Content-Type: application/json');

if (!empty($_GET["phrase"]) && !empty($_GET["ip"]) && !empty($_GET["geo"])) {
    $phrase = urldecode($_GET["phrase"]);
	$ip = urldecode($_GET["ip"]);
	$geo = urldecode($_GET["geo"]);
    require_once("TermHandler.php");
    $api = new TermHandler($phrase, $ip, $geo);
    $api->inputPhrase();
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a hash."}';
    die($error);
}

