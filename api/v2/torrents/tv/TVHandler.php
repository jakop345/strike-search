<?php

/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('C:/xampp/htdocs/apps/strike/api/v2/core/shrinkWrap.php');

class TVHandler
{

    public $phrase;
	public $imdbid;


    public function __construct($phrase, $imdbid)
    {

        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->phrase        = $this->shrinkWrapper->escape($phrase);
		 $this->imdbid        = $this->shrinkWrapper->escape($imdbid);
    }

    public function getShowInfo()
    {


        date_default_timezone_set('Europe/Berlin');


        $time  = microtime();
        $time  = explode(' ', $time);
        $time  = $time[1] + $time[0];
        $start = $time;

        $limit = 1000;
        $imdbid   = $this->imdbid;

        if (!empty($imdbid)) {

		    $imdbAPI     = json_decode($this->curl_file_get_contents("http://localhost/apps/strike/api/v2/media/imdb/?imdbid=$imdbid"), true);
		    $series_name = $imdbAPI["Title"];
            $run_date    = $imdbAPI["Year"];
            $rated       = $imdbAPI["Rated"];
            $released    = $imdbAPI["Released"];
            $run_time    = $imdbAPI["Runtime"];
            $genres      = $imdbAPI["Genre"];
            $director    = $imdbAPI["Director"];
            $writer      = $imdbAPI["Writer"];
            $actors      = $imdbAPI["Actors"];
            $plot        = $imdbAPI["Plot"];
            $languages   = $imdbAPI["Language"];
            $countries   = $imdbAPI["Country"];
            $poster      = $imdbAPI["Poster"];
            $metascore   = $imdbAPI["Metascore"];
            $imdb_rating = $imdbAPI["imdbRating"];
            $imdb_votes  = $imdbAPI["imdbVotes"];
            $series_id   = $imdbAPI["imdbID"];
            $trimmedImdb =  ltrim(ltrim($series_id, "tt"), '0');
            $type        = $imdbAPI["Type"];
		}


        $torrent_phrase = $this->phrase;
		if (!empty($series_name)) {
			 $torrent_phrase = $series_name;
		}
        $category       = "TV";
        $uploader       = "ettv";
        $uploader2      = "EZTV";
        $uploader3 = "TryMeS";
        $uploader4 = "rwdy";

        $torrent_phrase = str_replace("%", "\\%", $torrent_phrase);
        $torrent_phrase = str_replace("_", "\\_", $torrent_phrase);

        $shrinkWrap     = $this->shrinkWrapper;


        $arr = explode(' ', $torrent_phrase);


        $torrentResults = null;

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
      //  $torrentResults      = $shrinkWrap->query("SELECT torrent_hash, torrent_title, torrent_category, seeds, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) AND (uploader_username = '$uploader'  OR uploader_username = '$uploader2' OR uploader_username = '$uploader3' OR uploader_username = '$uploader4') AND torrent_category = 'TV' ORDER BY seeds DESC LIMIT $limit");
$torrentResults      = $shrinkWrap->query("SELECT torrent_hash, torrent_title, torrent_category, seeds, uploader_username FROM torrents WHERE MATCH (torrent_title) AGAINST('$searchString' IN BOOLEAN MODE) AND torrent_category = 'TV' ORDER BY seeds DESC LIMIT $limit");


        //$pattern       = "/(.*)\\.S?(\\d{1,2})E?(\\d{2})\\.(.*)/";
		$pattern       = "/(.*)\\.S?(\\d{1,2})E?(\\d{2})\\.(.*)/";
        $currentSeason = 0;
        $seasons       = array();
        //Can most defiantly be done better, no idea how functional this is. Job gets done.
        if (empty($torrentResults)) {
            http_response_code(404);
            $error = '{"statuscode":404,"message":"No TV Shows could be found."}';
            die($error);
        } else {

            http_response_code(200);
            $number_result = count($torrentResults);
            $time          = microtime();
            $time          = explode(' ', $time);
            $time          = $time[1] + $time[0];
            $finish        = $time;
            $total_time    = round(($finish - $start), 4);
            $encode_title  = rawurlencode($torrent_phrase);
			if (empty($imdbAPI )) {
            $omdbAPI = json_decode($this->curl_file_get_contents("http://omdbapi.com/?t=$encode_title&y=&plot=full&r=json"), true);

            $series_name = $omdbAPI["Title"];
            $run_date    = $omdbAPI["Year"];
            $rated       = $omdbAPI["Rated"];
            $released    = $omdbAPI["Released"];
            $run_time    = $omdbAPI["Runtime"];
            $genres      = $omdbAPI["Genre"];
            $director    = $omdbAPI["Director"];
            $writer      = $omdbAPI["Writer"];
            $actors      = $omdbAPI["Actors"];
            $plot        = $omdbAPI["Plot"];
            $languages   = $omdbAPI["Language"];
            $countries   = $omdbAPI["Country"];
            $poster      = $omdbAPI["Poster"];
            $metascore   = $omdbAPI["Metascore"];
            $imdb_rating = $omdbAPI["imdbRating"];
            $imdb_votes  = $omdbAPI["imdbVotes"];
            $series_id   = $omdbAPI["imdbID"];
            $trimmedImdb =  ltrim(ltrim($series_id, "tt"), '0');
            $type        = $omdbAPI["Type"];
			}
            foreach ($torrentResults as $key => &$value) { //first round, cleaning results and setting data
                unset($value["torrent_category"], $value["seeds"]);
                unset($value["file_names"], $value["file_sizes"]);
                $torrentTitle = preg_replace('-', ' ', $value["torrent_title"]);
                $torrentTitle = preg_replace('/\s+/', '.', $value["torrent_title"]);

                preg_match($pattern, $torrentTitle, $matches);

                $torrentTitle = str_replace('.', ' ', $matches[1]);

                if (strtolower($torrentTitle) != strtolower($torrent_phrase)) {

                    unset($torrentResults[$key]);
                    continue;
                }

                $season    = $matches[2];
                $seasonint = (int) ltrim($season, '0');
                if ($seasonint > $currentSeason) {
                    $currentSeason = $seasonint;
                }

                $episode = $matches[3];
                $quality = $this->getQuality($matches[4]);
                // print_r($matches);


                // $torrentHash = $value["torrent_hash"];

                $value["torrent_title"] = $torrentTitle;
                $value["season"]        = ltrim($season, '0');
                $value["episode"]       = ltrim($episode, '0');
                $value["quality"]       = $quality;


            }
            for ($i = 1; $i <= $currentSeason; ++$i) {
                $seasonNumber = array();
                array_push($seasons, $seasonNumber);
            }


            foreach ($torrentResults as $key => &$value) {
                $torrentSeason = (int) ltrim($value["season"], '0');
                array_push($seasons[$torrentSeason - 1], $value);

            }
            $totalSeason = count($seasons);
            $seriesArray = array();

            for ($i = 1; $i <= $totalSeason; ++$i) {
                $seasonNumber = $i - 1;
                usort($seasons[$seasonNumber], $this->build_sorter('episode'));
                end($seasons[$seasonNumber]);
                $last_id = key($seasons[$seasonNumber]);
                $totalEpisodes = (int) $seasons[$seasonNumber][$last_id]["episode"];
                $seasonArray = array_fill(0, $totalEpisodes, NULL);
                array_push($seriesArray, $seasonArray);
            }

            for ($i = 1; $i <= $totalSeason; ++$i) {
                $seasonNumber = $i - 1;
                $seasonUnchanged = $i;
                $totalEpisodes =  count($seriesArray[$seasonNumber]);

                for ($k = 1; $k <= $totalEpisodes; ++$k) {
                    $episodeAPI      = $shrinkWrap->query("SELECT * FROM imdb_episodes WHERE SeriesID = '$trimmedImdb' AND Season = '$seasonUnchanged' AND Episode = '$k' LIMIT $limit");

                    if (empty($episodeAPI)) {
                        $seriesArray[$seasonNumber]["$k"]["episode"] = "Unknown";
                        continue;
                    } else {
                        $episodeAPI =  $episodeAPI[0];
                        $seriesArray[$seasonNumber]["$k"]["episode_title"] = $episodeAPI["Title"];
                        $seriesArray[$seasonNumber]["$k"]["episode"]    = $episodeAPI["Episode"];
                        $seriesArray[$seasonNumber]["$k"]["season"]    = $episodeAPI["Season"];
                        $seriesArray[$seasonNumber]["$k"]["imdbID"]    = $episodeAPI["imdbId"];
                        $seriesArray[$seasonNumber]["$k"]["rated"]       = $episodeAPI["Rating"];
                        $seriesArray[$seasonNumber]["$k"]["aired"]     = $episodeAPI["Released"];
                        $seriesArray[$seasonNumber]["$k"]["runTime"]    = $episodeAPI["Runtime"];
                        $seriesArray[$seasonNumber]["$k"]["genre"]    = $episodeAPI["Genre"];
                        $seriesArray[$seasonNumber]["$k"]["director"]    = $episodeAPI["Director"];
                        $seriesArray[$seasonNumber]["$k"]["writer"]    = $episodeAPI["Writer"];
                        $seriesArray[$seasonNumber]["$k"]["actors"]    = $episodeAPI["Cast"];
                        $seriesArray[$seasonNumber]["$k"]["plot"]    = $episodeAPI["Plot"];
                        $seriesArray[$seasonNumber]["$k"]["language"]    = $episodeAPI["Language"];
                        $seriesArray[$seasonNumber]["$k"]["country"]    = $episodeAPI["Country"];
                        $seriesArray[$seasonNumber]["$k"]["awards"]    = $episodeAPI["Awards"];
                        $seriesArray[$seasonNumber]["$k"]["poster"]    = $episodeAPI["Poster"];
                        $seriesArray[$seasonNumber]["$k"]["metascore"]    = $episodeAPI["Metacritic"];
                        $seriesArray[$seasonNumber]["$k"]["imdbRating"]    = $episodeAPI["imdbRating"];
                        $seriesArray[$seasonNumber]["$k"]["imdbVotes"]    = $episodeAPI["imdbVotes"];
                        $seriesArray[$seasonNumber]["$k"]["torrents"] = array();
                        $seriesArray[$seasonNumber]["$k"]["torrents"]["1080p"] = array();
                        $seriesArray[$seasonNumber]["$k"]["torrents"]["720p"] = array();
                        $seriesArray[$seasonNumber]["$k"]["torrents"]["HDTV"] = array();
                        foreach ($torrentResults as $key => &$value) {
                            if ($value["season"]  == $seriesArray[$seasonNumber]["$k"]["season"] && $value["episode"] == $seriesArray[$seasonNumber]["$k"]["episode"])
                                switch ($value["quality"]) {
                                    case "1080p":
                                        array_push( $seriesArray[$seasonNumber]["$k"]["torrents"]["1080p"], $value["torrent_hash"]);
                                        break;
                                    case "720p":
                                        array_push( $seriesArray[$seasonNumber]["$k"]["torrents"]["720p"], $value["torrent_hash"]);
                                        break;
                                    case "HDTV":
                                        array_push( $seriesArray[$seasonNumber]["$k"]["torrents"]["HDTV"], $value["torrent_hash"]);
                                        break;
                                }
                        }

                        //     array_push($seriesArray, $seasonArray);



                    }



                }




            }


            $finalArray = array();
            for ($i = 1; $i <= $totalSeason; ++$i) {
                $finalArray["season_$i"] = $seriesArray[$i - 1];
            }

         //   unset($seriesArray[0][0]);
       //  print_r($finalArray);



            /*  foreach ($results as $key => &$value) {

            $torrentSeason = (int) ltrim($value["season"], '0');
            $episode = (int) ltrim($value["episode"], '0');
            if ($torrentSeason > 0) {

            $trimmedImdb =  ltrim(ltrim($series_id, "tt"), '0');
            $episodeAPI      = $shrinkWrap->query("SELECT * FROM imdb_episodes WHERE SeriesID = '$trimmedImdb' AND Season = '$torrentSeason' AND Episode = '$episode' LIMIT $limit");

            if (empty($episodeAPI)) {
            unset($results[$key]);
            continue;
            } else {
            $episodeAPI =  $episodeAPI[0];
            $value["episode_title"] = $episodeAPI["Title"];
            $value["rated"]       = $episodeAPI["Rating"];
            $value["aired"]     = $episodeAPI["Released"];

            $value["runTime"]    = $episodeAPI["Runtime"];
            $value["genre"]    = $episodeAPI["Genre"];
            $value["director"]    = $episodeAPI["Director"];
            $value["writer"]    = $episodeAPI["Writer"];
            $value["actors"]    = $episodeAPI["Cast"];
            $value["plot"]    = $episodeAPI["Plot"];
            $value["language"]    = $episodeAPI["Language"];
            $value["country"]    = $episodeAPI["Country"];
            $value["awards"]    = $episodeAPI["Awards"];
            $value["poster"]    = $episodeAPI["Poster"];
            $value["metascore"]    = $episodeAPI["Metacritic"];
            $value["imdbRating"]    = $episodeAPI["imdbRating"];
            $value["imdbVotes"]    = $episodeAPI["imdbVotes"];
            $value["imdbID"]    = $episodeAPI["imdbId"];
            $value["season"]    = $episodeAPI["Season"];
            $value["episode"]    = $episodeAPI["Episode"];
            unset($value["uploader_username"]);
            }



            array_push($seasons[$torrentSeason - 1], $value);
            }



            }*/

            // print_r($seasons);
            //  echo(json_encode($seasons));
            $json_dump = array(

                results => count($torrentResults),
                statuscode => 200,
                responsetime => $total_time,
                imdbId => $series_id,
				series_name => $series_name,
				run_date => $run_date,
				rated => $rated,
				released => $released,
				genres => $genres,
				director => $director,
				writer => $writer,
				cast => $actors,
				plot => $plot,
				languages => $languages,
				countries =>  $countries,
				poster => $poster,
				imdb_rating => $imdb_rating,
				imdb_votes => $imdb_votes,
                totalSeasons => $currentSeason,


                seasons => $finalArray
            );

            die(json_encode($json_dump));


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

        return $contents;
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

    private function build_sorter($key)
    {
        return function($a, $b) use ($key)
        {
            return strnatcmp($a[$key], $b[$key]);
        };
    }

    public function __destruct()
    {
        /* $time          = microtime();
        $time          = explode(' ', $time);
        $time          = $time[1] + $time[0];
        $finish        = $time;
        $total_time    = round(($finish - $start), 4);*/
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
}
