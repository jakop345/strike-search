<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('../core/shrinkWrap.php');

class PageHandler
{


    public $hashes;
    private $shrinkWrapper;


    public function __construct($hashes, $debug)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->hashes = $hashes;
    }

    public function getPage()
    {

   
        $torrent_hashes = rtrim(rtrim(($this->hashes), '/'), ',');
        $torrent_hashes = $this->shrinkWrapper->escape($torrent_hashes);
        $torrent_hashes = '\'' . $torrent_hashes . '\'';
       


        $shrinkWrap = $this->shrinkWrapper;


        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE torrent_page FROM torrents WHERE torrent_hash = $torrent_hashes LIMIT 1");
        if (empty($results)) {
            http_response_code(404);
            $error = '{"statuscode":404,"message":"No torrents found with provided hash"}';
            die($error);
        } else {
       
            http_response_code(200);
			echo(json_encode($results));
         
        }

    }


    public function __destruct()
    {

    }
}
