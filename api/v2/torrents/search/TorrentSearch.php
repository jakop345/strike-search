<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */

require_once('../core/shrinkWrap.php');

class TorrentSearch
{
    
    public $phrase;
    private $shrinkWrapper;
    public $category;
    public $subCategory;
    public $key;
    public $uploader;
    
    public function __construct($phrase, $category, $subCategory, $key, $uploader)
    {
        
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->phrase        = $this->shrinkWrapper->escape($phrase);
        $this->category      = $this->shrinkWrapper->escape($category);
        $this->subCategory   = $this->shrinkWrapper->escape($subCategory);
        $this->key           = $this->shrinkWrapper->escape($key);
        $this->uploader      = $this->shrinkWrapper->escape($uploader);
    }
    
    private function toByteSize($p_sFormatted)
    {
        $aUnits = array(
            'B' => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8
        );
        $sUnit  = strtoupper(trim(substr($p_sFormatted, -2)));
        if (intval($sUnit) !== 0) {
            $sUnit = 'B';
        }
        if (!in_array($sUnit, array_keys($aUnits))) {
            return false;
        }
        $iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));
        if (!intval($iUnits) == $iUnits) {
            return false;
        }
        return $iUnits * pow(1024, $aUnits[$sUnit]);
    }
    
    private function getAge($upload_date)
    {
		$ts = upload_date;
		$date = new DateTime("@$ts");
        $from    = new DateTime($date->format('Y-m-d'));
        $to      = new DateTime('today');
        $year    = $from->diff($to)->y;
        $months  = $from->diff($to)->m;
        $days    = $from->diff($to)->days;
        $hours   = $from->diff($to)->h;
        $minutes = $from->diff($to)->i;
        $age     = 'Fresh';
        if ($year > 0) {
            $age = $year == 1 ? $year . " year" : $year . " years";
        } else if ($months > 0) {
            $age = $months == 1 ? $months . " month" : $months . " months";
        } else if ($days > 0) {
            $age = $days == 1 ? $days . " day" : $days . " days";
        } else if ($hours > 0) {
            $age = $hours == 1 ? $hours . " hour" : $hours . " hours";
        } else if ($minutes > 0) {
            $age = $minutes == 1 ? $minutes . " minute" : $minutes . " minutes";
        }
        
        return $age;
    }
    
    
    private function is_sha1($sha1)
    {
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $sha1);
    }
    private function is_md5($md5 = '')
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/', $md5);
    }
    private function highLightResults($text, $words)
    {
        
        preg_match_all('~\w+~', $words, $m);
        if (!$m)
            return $text;
        $re = '~\\b(' . implode('|', $m[0]) . ')\\b~i';
        return preg_replace($re, '<span class="highlight">$0</span>', $text);
        
        
    }
    
    private function curl_file_get_contents($url)
    {
        $curl      = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
        
        curl_setopt($curl, CURLOPT_URL, $url); //The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); //The number of seconds to wait while trying to connect.	
        
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent); //The contents of the "User-Agent: " header to be used in a HTTP request.
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); //To fail silently if the HTTP code returned is greater than or equal to 400.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.	
        
        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }
    public function getTorrents()
    {
        
        
        date_default_timezone_set('Europe/Berlin');
        
        
        $time  = microtime();
        $time  = explode(' ', $time);
        $time  = $time[1] + $time[0];
        $start = $time;
        
        $limit = 100;
        $key   = $this->key;
        if ($key == "f9cE6zj7s7svvhPY22mgmh2182X814Im") {
            $limit = 15000;
        }
        
        
        $torrent_phrase = $this->phrase;
        $category       = $this->category;
        $uploader       = $this->uploader;
        $torrent_phrase = str_replace("%", "\\%", $torrent_phrase);
        $torrent_phrase = str_replace("_", "\\_", $torrent_phrase);
        $shrinkWrap     = $this->shrinkWrapper;
        
        
        $arr = explode(' ', $torrent_phrase);
        
        $categoryFlag = FALSE;
        $imdbFlag     = FALSE;
        $uploaderFlag = FALSE;
        if (!empty($category)) {
            $categoryFlag = TRUE;
        }
        if (!empty($uploader)) {
            $uploaderFlag = TRUE;
        }
        
        $torrent_subcategory = "";
        $hasSubCategory      = FALSE;
        if (!empty($this->subCategory)) {
            $torrent_subcategory = rtrim(rtrim(($this->subCategory), '/'), ',');
            $torrent_subcategory = $shrinkWrap->escape($torrent_subcategory);
            $hasSubCategory      = TRUE;
        }
        
        $results = null;
        
        $pieces         = explode(" ", $torrent_phrase);
        $newSearchArray = array();
        foreach ($pieces as &$value) {
            if (strpos($value, '-') === 0) {
                array_push($newSearchArray, $value);
            } else {
                $value = "+" . $value;
                array_push($newSearchArray, $value);
            }
        }
        $searcharray  = implode(";", $newSearchArray);
        $searchString = str_replace(";", " ", $searcharray);
        if ($uploaderFlag == TRUE) {
            $results = $shrinkWrap->query("SELECT torrent_hash, imdbid, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) AND uploader_username = '$uploader' ORDER BY seeds DESC LIMIT $limit");
        } else if ($categoryFlag == FALSE) {
            $results = $shrinkWrap->query("SELECT torrent_hash, imdbid, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) ORDER BY seeds DESC LIMIT $limit");
        } else if ($categoryFlag == TRUE && $hasSubCategory == FALSE) {
            $results = $shrinkWrap->query("SELECT torrent_hash, imdbid, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) AND torrent_category = '$category' ORDER BY seeds DESC LIMIT $limit");
            
        } else if ($categoryFlag == TRUE && $hasSubCategory == TRUE) {
            $results = $shrinkWrap->query("SELECT torrent_hash, torrent_title, imdbid, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) AND torrent_category = '$category' AND sub_category = '$torrent_subcategory' ORDER BY seeds DESC LIMIT $limit");
        }
        
        //Can most defiantly be done better, no idea how functional this is. Job gets done.
        if (empty($results)) {
            http_response_code(404);
            $error = '{"statuscode":404,"message":"No torrents found."}';
            die($error);
        } else {
            http_response_code(200);
            $number_result = count($results);
            $time          = microtime();
            $time          = explode(' ', $time);
            $time          = $time[1] + $time[0];
            $finish        = $time;
            $total_time    = round(($finish - $start), 4);
            foreach ($results as &$value) {
                
                $torrentTitle        = $value["torrent_title"];
                $torrentHash         = $value["torrent_hash"];
                $upload_date         = $value["upload_date"];
                $hash                = $value["torrent_hash"];
                $value['page']       = "https://getstrike.net/torrents/$hash";
                $value['rss_feed']   = "https://getstrike.net/torrents/$hash?rss=1";
                $encodedTrackers     = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
                $encodedTitle        = urlencode($torrentTitle);
                $magnentString       = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
                $value['magnet_uri'] = $magnentString;
					unset($value['magnet_uri']);
                $value['size']       = round($this->toByteSize($value['size']));
                
                if (empty($value['imdbid'])) {
                    $imdbFlag == TRUE;
                }
                
                $category = $value['torrent_category'];
                if ($category == "Movies" || $category == "TV" && $imdbFlag == TRUE) {
                 //   $imdbJson        = json_decode(file_get_contents("http://localhost/apps/strike/api/v2/torrents/imdb/?hash=$torrentHash"), true);
               //     $imdbID          = $imdbJson["message"];
                  //  $value['imdbid'] = $imdbID;
                }
            }
            
            $json_dump = array(
                results => count($results),
                statuscode => 200,
                responsetime => $total_time,
                
                torrents => $results
            );
            
            die(json_encode($json_dump));
            
            
        }
        
    }
    
    
    public function __destruct()
    {
        
    }
}
