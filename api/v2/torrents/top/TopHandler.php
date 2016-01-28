<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('C:/xampp/htdocs/apps/strike/api/v2/core/shrinkWrap.php');

class TopHandler
{
    
    
    public $category;
    public $subCategory;
    private $shrinkWrapper;
    
    
    public function __construct($category, $subCategory)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->category      = $category;
        $this->subCategory   = $subCategory;
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
    
    public function fetchTop100()
    {
        
        date_default_timezone_set('Europe/Berlin');
        
        $time  = microtime();
        $time  = explode(' ', $time);
        $time  = $time[1] + $time[0];
        $start = $time;
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        $torrent_category    = rtrim(rtrim(($this->category), '/'), ',');
        $torrent_category    = $shrinkWrap->escape($torrent_category);
        $unquotedCategory    = strtolower($this->category);
        $torrent_subcategory = "";
        $hasSubCategory      = FALSE;
        if (!empty($this->subCategory)) {
            $torrent_subcategory = rtrim(rtrim(($this->subCategory), '/'), ',');
            $torrent_subcategory = $shrinkWrap->escape($torrent_subcategory);
            $hasSubCategory      = TRUE;
        }
        
        
        
        
        
        if ($unquotedCategory == "all") {
            
            $results = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_hash, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents ORDER BY seeds DESC LIMIT 100");
            if (empty($results)) {
                
                http_response_code(500);
                $error = '{"statuscode":500,"message":"Something went wrong internally"}';
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
                    $torrentHash         = $value["torrent_hash"];
                    $torrentTitle        = $value["torrent_title"];
                    $upload_date         = $value["upload_date"];
                    $hash                = $value["torrent_hash"];
                    $value['page']       = "https://getstrike.net/torrents/$hash";
                    $value['rss_feed']   = "https://getstrike.net/torrents/$hash?rss=1";
                    $encodedTrackers     = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
                    $encodedTitle        = urlencode($torrentTitle);
                    $magnentString       = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
                    $value['magnet_uri'] = $magnentString;
						unset($value['magnet_uri']);
                    
                    
                    $value['size'] = $this->toByteSize($value['size']);
                }
                
                $json_dump = array(
                    results => count($results),
                    statuscode => 200,
                    responsetime => $total_time,
                    
                    torrents => $results
                );
                
                echo json_encode($json_dump);
                
                die();
            }
            
        } else if ($unquotedCategory != "all" && $hasSubCategory) {
            
            $results = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_hash, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE torrent_category = '$torrent_category' AND sub_category = '$torrent_subcategory' ORDER BY seeds DESC LIMIT 100");
            if (empty($results)) {
                
                http_response_code(404);
                $error = '{"statuscode":404,"message":"No torrents could be found with your specified category"}';
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
                    $torrentHash  = $value["torrent_hash"];
                    $torrentTitle = $value["torrent_title"];
                    
                    $upload_date         = $value["upload_date"];
                    $hash                = $value["torrent_hash"];
                    $value['page']       = "https://getstrike.net/torrents/$hash";
                    $value['rss_feed']   = "https://getstrike.net/torrents/$hash?rss=1";
                    $encodedTrackers     = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
                    $encodedTitle        = urlencode($torrentTitle);
                    $magnentString       = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
                    $value['magnet_uri'] = $magnentString;
						unset($value['magnet_uri']);
                    
                    
                    $value['size'] = $this->toByteSize($value['size']);
                }
                
                $json_dump = array(
                    results => count($results),
                    statuscode => 200,
                    responsetime => $total_time,
                    
                    torrents => $results
                );
                
                die(json_encode($json_dump));
                
                
            }
        } else {
            $results = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_hash, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE torrent_category = '$torrent_category' ORDER BY seeds DESC LIMIT 100");
            if (empty($results)) {
                
                http_response_code(404);
                $error = '{"statuscode":404,"message":"No torrents could be found with your specified category"}';
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
                    $torrentHash   = $value["torrent_hash"];
                    $torrentTitle  = $value["torrent_title"];
                    $upload_date   = $value["upload_date"];
                    $hash          = $value["torrent_hash"];
                    $value['page'] = "https://getstrike.net/torrents/$hash";
                    
                    $value['rss_feed']   = "https://getstrike.net/torrents/$hash?rss=1";
                    $encodedTrackers     = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
                    $encodedTitle        = urlencode($torrentTitle);
                    $magnentString       = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
                    $value['magnet_uri'] = $magnentString;
						unset($value['magnet_uri']);
                
                    
                    $value['size'] = $this->toByteSize($value['size']);
                }
                
                $json_dump = array(
                    results => count($results),
                    statuscode => 200,
                    responsetime => $total_time,
                    
                    torrents => $results
                );
                
                
                
                echo json_encode($json_dump);
                
                die();
            }
        }
        
    }
    
    
    
    
    
    
    public function __destruct()
    {
        
    }
}