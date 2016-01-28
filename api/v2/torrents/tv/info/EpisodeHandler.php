<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('C:/xampp/htdocs/apps/strike/api/v2/core/shrinkWrap.php');

class EpisodeHandler
{
    
    public $hash;
    public $shrinkWrapper;
    
    
    public function __construct($hash)
    {
        
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->hash          = $this->shrinkWrapper->escape($hash);
    }
    
    
    
    
    
    private function getQuality($string)
    {
        if (strpos($string, '720') !== false) {
            return "720p";
        } else if (strpos($string, '1080') !== false) {
            return "1080p";
        } else if (strpos($string, 'HDTV') !== false) {
            return "HDTV";
        } else {
            return "Unknown";
        }
    }
    
    private function curl_file_get_contents($url)
    {
        $curl = curl_init();
        
        
        curl_setopt($curl, CURLOPT_URL, $url); //The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true); // some output will go to stderr / error_log
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.001 (windows; U; NT4.0; en-US; rv:1.0) Gecko/25250101');
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); //To fail silently if the HTTP code returned is greater than or equal to 400.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE); //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.
        
        
        $contents = curl_exec($curl);
        curl_close($curl);
        return utf8_decode($contents);
    }
    
    
    private function build_sorter($key)
    {
        return function($a, $b) use ($key)
        {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
    
    public function getShowInfo()
    {
        
        
        date_default_timezone_set('Europe/Berlin');
        
        
        $time  = microtime();
        $time  = explode(' ', $time);
        $time  = $time[1] + $time[0];
        $start = $time;
        
        $limit      = 1000;
        $hash       = $this->hash;
        $shrinkWrap = $this->shrinkWrapper;
        
        $imdbIdApi     = json_decode($this->curl_file_get_contents("http://localhost/apps/strike/api/v2/torrents/imdb/?hash=$hash"), true);
        $seriesId      = $imdbIdApi["message"];
        $trimmedImdbId = ltrim(ltrim($imdbIdApi["message"], "tt"), '0');
        
        $category       = "TV";
        $torrentResults = $shrinkWrap->query("SELECT torrent_hash, torrent_title FROM torrents WHERE torrent_hash = '$hash'");
        if (empty($torrentResults)) {
            
        } else {
            
            
            $value = $torrentResults[0];
            
            $pattern      = "/(.*)\\.S?(\\d{1,2})E?(\\d{2})\\.(.*)/";
            $torrentTitle = preg_replace('-', ' ', $value["torrent_title"]);
            $torrentTitle = preg_replace('/\s+/', '.', $value["torrent_title"]);
            
            preg_match($pattern, $torrentTitle, $matches);
            $torrentTitle           = str_replace('.', ' ', $matches[1]);
			
            $season                 = $matches[2];
            $seasonint              = (int) ltrim($season, '0');
		
            $episode                = $matches[3];
				 $episodeint              = (int) ltrim($episode, '0');
            $quality                = $this->getQuality($matches[4]);
            $value["torrent_title"] = $torrentTitle;
            $value["season"]        = ltrim($season, '0');
            $value["episode"]       = ltrim($episode, '0');
			
            $value["quality"]       = $quality;
		
            $episodeAPI             = $shrinkWrap->query("SELECT * FROM imdb_episodes WHERE SeriesID = '$trimmedImdbId' AND Season = '$seasonint' AND Episode = '$episodeint' LIMIT $limit");
            
            $time       = microtime();
            $time       = explode(' ', $time);
            $time       = $time[1] + $time[0];
            $finish     = $time;
            $total_time = round(($finish - $start), 4);
            $json_dump  = array(
                
                results => count(1),
                statusCode => 200,
                responseTime => $total_time,
                seriesId => $seriesId,
                seriesName => $value["torrent_title"],
                episodeInfo => $episodeAPI[0]
            );
            
            die(json_encode($json_dump));
            
            
            // var_dump($value);
            
            
        }
        
        
        
        
    }
    
    
    public function __destruct()
    {
        /* $time          = microtime();
        $time          = explode(' ', $time);
        $time          = $time[1] + $time[0];
        $finish        = $time;
        $total_time    = round(($finish - $start), 4);*/
    }
}
