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
header('Content-Type: application/json; charset=utf-8');
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



if (!empty($_GET["video"])) {
    $video   = urldecode($_GET["video"]);
	$url = "https://www.youtube.com/watch?v=" . $video;
	$proxy = '168.63.24.174:8122';
	 $curl = curl_init();


        curl_setopt($curl, CURLOPT_URL, $url); //The URL to fetch. This can also be set when initializing a session with curl_init().
		//curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true); // some output will go to stderr / error_log
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.001 (windows; U; NT4.0; en-US; rv:1.0) Gecko/25250101');
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); //To fail silently if the HTTP code returned is greater than or equal to 400.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.


        $contents = curl_exec($curl);
		$encodedData = base64_encode($contents);
		$jsonData = "{\"statuscode\":200,\"message\":\"$encodedData\"}";
		die($jsonData);
} else {
    http_response_code(404);
    $error = '{"statuscode":404,"message":"Please enter a video link."}';
    die($error);
}
