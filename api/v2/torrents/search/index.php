<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */
ini_set('max_execution_time', 60); // prints 10 (or whatever the old value was)
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



if (!empty($_GET["phrase"])) {
    $phrase   = urldecode($_GET["phrase"]);
    $category = "";
	$key = "";
	$limit = 4;
	
	$uploader = "";
    if (!empty($_GET["category"])) {
        $category = urldecode($_GET["category"]);
    }
    $subCategory = "";
    if (!empty($_GET["subcategory"])) {
        $subCategory = urldecode($_GET["subcategory"]);

    }
	 if (!empty($_GET["key"])) {
        $key = urldecode($_GET["key"]);
		$limit = 1;
    }
	
	if (!empty($_GET["uploader"])) {
        $uploader = urldecode($_GET["uploader"]);

    }
	
    $testPhrase = str_replace(' ', '', $phrase);
    if (strlen($testPhrase) < $limit) {
        http_response_code(404);
        $error = '{"statuscode":404,"message":"Your query must be at least 4 characters long without white space"}';
        die($error);
    }
	

    require_once("TorrentSearch.php");
    $torrent = new TorrentSearch($phrase, $category, $subCategory, $key, $uploader);
    $torrent->getTorrents();
  
    
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a phrease."}';
    die($error);
}
