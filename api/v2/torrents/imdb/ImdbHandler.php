<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */



require_once('../core/shrinkWrap.php');

class ImdbHandler
{
    
    
    public $hashes;
    private $shrinkWrapper;
    
    
    
    public function __construct($hashes)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->hashes        = $hashes;
        
    }
    
    
    public function get_url($url)
    {
        //user agent is very necessary, otherwise some websites like google.com wont give zipped content
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-Language: en-US,en;q=0.8rn" . "Accept-Encoding: gzip,deflate,sdchrn" . "Accept-Charset:UTF-8,*;q=0.5rn",
                'ignore_errors' => true
            )
        );
        
        $context = stream_context_create($opts);
        $content = file_get_contents($url, false, $context);
        
        //If http response header mentions that content is gzipped, then uncompress it
        foreach ($http_response_header as $c => $h) {
            if (stristr($h, 'content-encoding') and stristr($h, 'gzip')) {
                //Now lets uncompress the compressed data
                $content = gzinflate(substr($content, 10, -8));
            }
        }
        
        return $content;
    }
    
    public function get_string_between($string, $start, $end)
    {
        $string = " " . $string;
        $ini    = strpos($string, $start);
        if ($ini == 0)
            return "";
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    
    
    public function getDescription()
    {
        
        
        $torrent_hashes = rtrim(rtrim(($this->hashes), '/'), ',');
        $torrent_hashes = $this->shrinkWrapper->escape($torrent_hashes);
        $torrent_hashes = '\'' . $torrent_hashes . '\'';
        $hash           = $this->hashes;
        
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        
        
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE imdbid FROM torrents WHERE torrent_hash = $torrent_hashes LIMIT 1");
		
		$mysqlID = $results[0]["imdbid"];
	
        if (empty($mysqlID)) {
            
            $pageJson     = json_decode(file_get_contents("http://localhost/apps/strike/api/v2/torrents/page/?hash=$hash"), true);
			
            $torrent_page = $pageJson[0]["torrent_page"];
            $kickassHTML  = $this->get_url($torrent_page);
            $imdbIDLink   = trim($this->get_string_between($kickassHTML, "<strong>IMDb link:</strong>", "</li>"));
            $imdbID       = trim($this->get_string_between($imdbIDLink, "\"http://blankrefer.com/?http://www.imdb.com/title/", "/\">"));
            if (empty($imdbID)) {
                $imdbID = "none";
				$insertImdb = $shrinkWrap->query("UPDATE torrents SET imdbid = '$imdbID' WHERE torrent_hash = $torrent_hashes");
                http_response_code(404);
                $json = "{\"statuscode\":404,\"message\":\"$imdbID\"}";
                die($json);
            }
            $insertImdb = $shrinkWrap->query("UPDATE torrents SET imdbid = '$imdbID' WHERE torrent_hash = $torrent_hashes");
            http_response_code(200);
            $json = "{\"statuscode\":200,\"message\":\"$imdbID\"}";
            echo ($json);
        } else {
            http_response_code(200);
            $imdbID = $results[0]["imdbid"];
            $json   = "{\"statuscode\":200,\"message\":\"$imdbID\"}";
            echo ($json);
        }
        
        
        
    }
    
    
    public function __destruct()
    {
        
    }
}
