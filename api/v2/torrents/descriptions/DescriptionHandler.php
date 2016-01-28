<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */



require_once('../core/shrinkWrap.php');

class DescriptionHandler
{
    
    
    public $hashes;
    private $shrinkWrapper;
    
    
    public function __construct($hashes, $debug)
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
    
    //Scrape kickass for descriptions
    public function getDescription()
    {
        
        
        $torrent_hashes = rtrim(rtrim(($this->hashes), '/'), ',');
        $torrent_hashes = $this->shrinkWrapper->escape($torrent_hashes);
        $torrent_hashes = '\'' . $torrent_hashes . '\'';
        $hash           = $this->hashes;
        
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        
        
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE description FROM descriptions WHERE torrent_hash = $torrent_hashes LIMIT 1");
        if (empty($results)) {
            $pageJson           = json_decode(file_get_contents("http://getstrike.net/api/v2/torrents/page/?hash=$hash"), true);
            $torrent_page       = $pageJson[0]["torrent_page"];
            $kickassHTML        = $this->get_url($torrent_page);
            $kickAssDescription = $this->get_string_between($kickassHTML, "<div class=\"textcontent\" id=\"desc\">", "<h2>");
            $content            = str_replace("//kastatic.com/images/blank.gif", "http://getstrike.net/img/blank.gif", $kickAssDescription);
            if (!strlen($content)) {
                $content = "This torrent has no description";
            } 
               
            
            
            $storecontent      = base64_encode($content);
            $insertDescription = $shrinkWrap->query("REPLACE INTO descriptions (torrent_hash, description) VALUES ($torrent_hashes, '$storecontent')");
            http_response_code(200);
            $json = "{\"statuscode\":200,\"message\":\"$storecontent\"}";
            echo ($json);
            
            
        } else {
            
            http_response_code(200);
            $message = $results[0]["description"];
            $json    = "{\"statuscode\":200,\"message\":\"$message\"}";
            
            echo $json;
            
        }
        
    }
    
    
    public function __destruct()
    {
        
    }
}
