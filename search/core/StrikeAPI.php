<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


class StrikeAPI
{

    public $hash;
    public $json;

    public function __construct($hash)
    {
        $this->hash = $hash;
    }


    private function is_sha1($sha1)
    {
        return (bool)preg_match('/^[0-9a-f]{40}$/i', $sha1);
    }

    private function is_md5($md5 = '')
    {
        return (bool)preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    private function fetchJson()
    {
	$url = "https://getstrike.net/api/v2/torrents/info/?hashes=" .$this->hash;
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_VERBOSE, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        $jsonData = json_decode(curl_exec($curlSession), true);

        curl_close($curlSession);
        return $jsonData;
    }

    public function getJson()
    {
        return $this->json;
    }


    public function grabInfo()
    {
        if (($this->is_md5($this->hash)) || $this->is_sha1($this->hash)) {
            $this->json = $this->fetchJson();
        } else {
			
            http_response_code(404);
			  header('HTTP/1.1 404 Not Found'); 
        }
    }


    public function __destruct()
    {

    }
}
