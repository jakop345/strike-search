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
    
    
    public $imdbid;
    private $shrinkWrapper;
    
    
    
    public function __construct($imdbid)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->imdbid        = $imdbid;
        
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
        
        
      
        $imdbid = $this->shrinkWrapper->escape($this->imdbid);
       
        
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        
        
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE * FROM imdb_info WHERE imdbID = '$imdbid' LIMIT 1");
        if (empty($results)) {
            
           http_response_code(404);
            
            $json   = "{\"statuscode\":404,\"message\":\"No imdb information found, id stored for indexing.\"}";
            echo ($json);
        } else {
            http_response_code(200);
            $json = json_encode($results[0]);
            echo ($json);
        }
        
        
        
    }
    
    
    public function __destruct()
    {
        
    }
}
