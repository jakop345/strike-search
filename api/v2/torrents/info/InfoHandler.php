<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('../core/Torrent.php');
require_once('../core/shrinkWrap.php');

class InfoHandler
{
    
    
    public $hashes;
    private $shrinkWrapper;
    private $debug;
    
    
    public function __construct($hashes, $debug)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->hashes        = $hashes;
        $this->debug         = $debug;
    }
    
    
    private function isValidMd5($md5 = '')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
    
    private function downloadFile($url, $path)
    {
        
        $output_filename = $path;
        
        $host = $url;
        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        $result = curl_exec($ch);
        curl_close($ch);
        // the following lines write the contents to a file in the same directory (provided permissions etc)
        $fp = fopen($output_filename, 'w');
        fwrite($fp, $result);
        fclose($fp);
    }
    
    private function getFileInformation($hash)
    {
        $shrinkWrap = $this->shrinkWrapper;
        
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE * FROM  `file_info` WHERE  `torrent_hash` = '$hash'");
        
        if (empty($results)) {
            $torrentFile  = "C:/xampp/htdocs/apps/strike/torrents/api/download/" . $hash . ".torrent";
            $torrentTitle = $value["torrent_title"];
            if (!file_exists($torrentFile)) {
				$remoteTorrent = "http://torcache.net/torrent/$hash.torrent";
				$file_headers = @get_headers($file);
				if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
			$exists = false;
			}
			else {
     $this->downloadFile($remoteTorrent, "C:/xampp/htdocs/apps/strike/torrents/api/download/" . $hash . ".torrent");
			}
                
            }
            $torrent          = new Torrent(($torrentFile));
            $file_names       = array_keys($torrent->content());
            $file_lengths     = array_values($torrent->content());
            $fileNameRecord   = "";
            $fileLengthRecord = "";
            foreach ($file_names as &$value) {
			
				
					 $fileNameRecord .= strlen($fileNameRecord) == 0 ? trim($value) : ", " . trim($value);
				
               
            }
            foreach ($file_lengths as &$value) {
                $fileLengthRecord .= strlen($fileLengthRecord) == 0 ? trim($value) : ", " . trim($value);
            }
            if (strpos($fileNameRecord,'xampp') !== false) {
			$fileNameRecord = "Error";
			}
            $lengthArray = json_decode('[' . $fileLengthRecord . ']', true);
            $nameArray   = explode(',', $fileNameRecord);
            
            $fileData         = array(
                file_names => $nameArray,
                file_lengths => $lengthArray
                
            );
            $fileNameRecord   = $this->shrinkWrapper->escape($fileNameRecord);
            $fileLengthRecord = $this->shrinkWrapper->escape($fileLengthRecord);
            
            $insertFileNames = $shrinkWrap->query("REPLACE into file_info (torrent_hash, file_names, file_sizes) values(\"$hash\", \"$fileNameRecord\", \"$fileLengthRecord\")");
            return $fileData;
        } else {
            
            $fileLengthRecord = $results[0]["file_sizes"];
            $fileNameRecord   = $results[0]["file_names"];
             if (strpos($fileNameRecord,'xampp') !== false) {
			$fileNameRecord = "Error";
			}
            $lengthArray = json_decode('[' . $fileLengthRecord . ']', true);
            $nameArray   = explode(',', $fileNameRecord);
            $fileData    = array(
                file_names => $nameArray,
                file_lengths => $lengthArray
                
            );
            
            
            return $fileData;
        }
        
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
    public function getHashInformation()
    {
        
        $time           = microtime();
        $time           = explode(' ', $time);
        $time           = $time[1] + $time[0];
        $start          = $time;
        $torrent_hashes = rtrim(rtrim(($this->hashes), '/'), ',');
        $torrent_hashes = $this->shrinkWrapper->escape($torrent_hashes);
        $imdbFlag     = FALSE;
        
        if (strpos($torrent_hashes, ',') !== false) {
            $torrentHashArray       = explode(',', $torrent_hashes);
            $uniqueTorrentHashArray = array_unique($torrentHashArray);
            $hashAmount             = count($uniqueTorrentHashArray);
            if ($hashAmount > 50) {
                http_response_code(403);
                $error = '{"statuscode":403,"message":"You\'re only allowed 50 hashes per query"}';
                die($error);
            }
            foreach ($torrentHashArray as &$value) {
                $value = '\'' . $value . '\'';
            }
            $torrent_hashes = implode(",", $torrentHashArray);
        } else {
            $torrent_hashes = '\'' . $torrent_hashes . '\'';
        }
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE  torrent_hash, imdbid, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE torrent_hash IN ( $torrent_hashes ) LIMIT 50");
        if (empty($results)) {
            http_response_code(404);
            $error = '{"statuscode":404,"message":"No torrents found with provided hashes"}';
            die($error);
        } else {
            foreach ($results as &$value) {
                $torrentHash  = $value["torrent_hash"];
                $torrentTitle = $value["torrent_title"];
                if (empty($value["file_names"])) {
                    
                    $fileInfo = $this->getFileInformation($value["torrent_hash"]);
                    unset($value["file_names"], $value["file_sizes"]);
                    $value['file_info'] = $fileInfo;
                    
                    
                } else {
                    
                    
                    $fileLengthRecord = $value["file_sizes"];
                    $fileNameRecord   = $value["file_names"];
                    
                    $lengthArray = json_decode('[' . $fileLengthRecord . ']', true);
                    $nameArray   = explode(',', $fileNameRecord);
                    $fileData    = array(
                        file_names => $nameArray,
                        file_lengths => $lengthArray
                        
                    );
                    unset($value["file_names"], $value["file_sizes"]);
                    $value['file_info'] = $fileData;
                }
                
                $encodedTrackers     = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
                $encodedTitle        = urlencode($torrentTitle);
                $magnentString       = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
                $value['magnet_uri'] = $magnentString;
					unset($value['magnet_uri']);
                
                $length        = $value['file_info']["file_lengths"];
                $totalBytes    = array_sum($length);
               	$value['size'] = round($this->toByteSize($value['size']));
				if (empty($value['imdbid'])) {
                    $imdbFlag == TRUE;
                }
                
                $category = $value['torrent_category'];
                if ($category == "Movies" || $category == "TV" && $imdbFlag == TRUE) {
                    $imdbJson        = json_decode(file_get_contents("http://localhost/apps/strike/api/v2/torrents/imdb/?hash=$torrentHash"), true);
                    $imdbID          = $imdbJson["message"];
                    $value['imdbid'] = $imdbID;
                }
				
                
            }
            $time       = microtime();
            $time       = explode(' ', $time);
            $time       = $time[1] + $time[0];
            $finish     = $time;
            $total_time = round(($finish - $start), 4);
            http_response_code(200);
            
            $json_dump = array(
                results => count($results),
                statuscode => 200,
                responsetime => $total_time,
                
                torrents => $results
            );
            if ($this->debug == TRUE) {
                print_r($json_dump);
            } else {
                echo (json_encode($json_dump));
            }
            
        }
        
    }
    
    
    public function __destruct()
    {
        
    }
}
