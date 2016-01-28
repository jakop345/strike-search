<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('shrinkWrap.php');
require_once('EpiCurl.php');
class TorrentSearch
{

    public $phrase;
    private $shrinkWrapper;


    public function __construct($phrase)
    {

        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->phrase        = $this->shrinkWrapper->escape($phrase);
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

	private function isValidTimeStamp($timestamp)
{
    return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}
    private function getAge($upload_date)
    {
		
		
		$ts = $upload_date;
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
    // Function to get the client IP address
    private function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
	
	  private function format_size($bytes, $unit = "", $decimals = 2) {
 $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 
 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
 
 $value = 0;
 if ($bytes > 0) {
 // Generate automatic prefix by bytes 
 // If wrong prefix given
 if (!array_key_exists($unit, $units)) {
 $pow = floor(log($bytes)/log(1024));
 $unit = array_search($pow, $units);
 }
 
 // Calculate byte value by prefix
 $value = ($bytes/pow(1024,floor($units[$unit])));
 }
 
 // If decimals is not numeric or decimals is less than 0 
 // then set default value
 if (!is_numeric($decimals) || $decimals < 0) {
 $decimals = 2;
 }
 
 // Format output
 return sprintf('%.' . $decimals . 'f '.$unit, $value);
  }

    public function getTorrents()
    {



        date_default_timezone_set('Europe/Berlin');

        $time  = microtime();
        $time  = explode(' ', $time);
        $time  = $time[1] + $time[0];
        $start = $time;


        $torrent_phrase = $this->phrase;
        $torrent_phrase = str_replace("%", "\\%", $torrent_phrase);
        $torrent_phrase = str_replace("_", "\\_", $torrent_phrase);
        $shrinkWrap     = $this->shrinkWrapper;


        $arr      = explode(' ', $torrent_phrase);
        $likeFlag = FALSE;
        $hashFlag = FALSE;


        $results = null;

        if (($this->is_md5($torrent_phrase)) || $this->is_sha1($torrent_phrase)) {
            $hashFlag = TRUE;
            $results  = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_hash, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE torrent_hash='$torrent_phrase' ORDER BY seeds DESC LIMIT 500");
        } else if ($likeFlag == FALSE && $hashFlag == FALSE) {

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



            $results = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_hash, torrent_title, torrent_category, sub_category, seeds,leeches, file_count, size, upload_date, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) ORDER BY seeds DESC LIMIT 1000");
        }

        //Can most defiantly be done better, no idea how functional this is. Job gets done.
        if (empty($results)) {
            die('<div class="no-torrents">No torrents could be found</div>');
        } else {
            $encodephrase  = urlencode($torrent_phrase);
            $ip            = $this->get_client_ip();
            $country_code  = $_SERVER["HTTP_CF_IPCOUNTRY"];
          // $set           = file_get_contents("http://localhost/apps/strike/api/v2/torrents/terms/?phrase=$encodephrase&ip=$ip&geo=$country_code");
			
			$number_result = count($results);
            $time          = microtime();
            $time          = explode(' ', $time);
            $time          = $time[1] + $time[0];
            $finish        = $time;
            $total_time    = round(($finish - $start), 4);
            $stats         = <<<END
    <div id="stats">
               
                    <div class="stats">
                        <div class="row">
                            <div class="col-md-6">$number_result torrents <span class="muted">($total_time)</span></div>
                            <div class="col-md-6 text-right">
                                <a href="#" id="toggle-lock"><i class="fa fa-lock fa-lg"></i></a>
                                <a href="#" id="bars"><i class="fa fa-bars fa-lg"></i></a>
                            </div>
                        </div>
                    </div>
                
                </div>

END;

            $startTable = <<<END
     <div id="results">

                    <!-- result, sortable table -->
                    <table class='table sortable table-hover pullDown' id="table">
                        <thead>
                            <tr>
                                <th style="width: 50%; vertical-align: middle;" rowspan="2" class='az' data-defaultsign="nospan" data-defaultsort="asc">Title</th>
                            </tr>
                            <tr>
                                <th style="width: 20%">Age</th>
                                <th style="width: 20%">Size</th>
                                <th style="width: 20%">Seeders</th>
                                <th style="width: 20%">Leechers</th>
                            </tr>
                        </thead>
                        <tbody>
END;




            echo ($stats);
            echo ($startTable);


            foreach ($results as &$value) {

                $upload_date = $value["upload_date"];
				
                $hash        = strtoupper($value["torrent_hash"]);


                $title         = $this->highLightResults($value["torrent_title"], $torrent_phrase);
                $age           = $this->getAge($upload_date);
			
				
                $category      = $value["torrent_category"];
                $sub_category  = $value["sub_category"];
                $seeders       = $value["seeds"];
                $leechers      = $value["leeches"];
                $file_count    = $value["file_count"];
                $size          = $value["size"];

                $formattedSize    = $this->format_size($size);
                $username      = $value["uploader_username"];
                $url           = "http://torcache.net/torrent/$hash.torrent";
                $site_title    = "Download";
                $download_link = "<a href=https://getstrike.net/torrents/$hash>$title</a>";
			
				$ts = $upload_date;
				$date = new DateTime("@$ts");
				
                $sortDate      = $date->format('Y-m-d');
			
                $now           = time(); // or your date as well
                $torrent_date  = strtotime($sortDate);
                $datediff      = $now - $torrent_date;
                $sortData      = floor($datediff / (60 * 60 * 24));
                echo ("<tr class='clickable-row' data-href='https://getstrike.net/torrents/$hash'><td>" . $download_link . "</td><td data-value=\"$sortData\">" . $age . "</td><td data-value=\"$size\">" . $formattedSize . "</td><td><span class=\"seeders\">" . $seeders . "</span></td><td><span class=\"leechers\">" . $leechers . "</span></td></tr>");

            }


            echo "</tbody>"; //Close the table in HTML
            echo "</table>";
            echo "</div>";
            //   var_dump($results);
			//$mc = EpiCurl::getInstance();
			//$url = "http://localhost/apps/strike/api/v2/torrents/terms/?phrase=$encodephrase&ip=$ip&geo=$country_code";
			//$track = $mc->addURL($url);
			//$track->code;
            die();
        }

    }


    public function __destruct()
    {

    }
}
