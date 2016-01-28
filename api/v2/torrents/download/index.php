
<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 2/26/2015
 * Time: 1:50 PM
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');


function downloadFile($url, $path)
{
    $output_filename = $path;
    $host            = $url;
    $ch              = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $result   = curl_exec($ch);
    /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 404) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    
    
    // the following lines write the contents to a file in the same directory (provided permissions etc)
    
    $fp = fopen($output_filename, 'w');
    fwrite($fp, $result);
    fclose($fp);
}

if (isset($_GET['hash'])) {
   http_response_code(200);
                $error = '{"statuscode":200,"message":"Download is not available anymore"}';
                die($error);
}
