<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 *
 *
 *
 * Don't freak out, this code is left over from when we did the live feedpage, all this data was destroyed every 24 hours.
 */



require_once('../core/shrinkWrap.php');

class TermHandler

{
    
    
    public $phrase;
    private $shrinkWrapper;
    private $ip;
    private $geo;
    
    
    public function __construct($phrase, $ip, $geo)
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        $this->phrase        = $phrase;
        $this->ip            = $ip;
        $this->geo           = $geo;
    }
    
    public function inputPhrase()
    {
        
        
        $torrent_phrase = rtrim(rtrim(($this->phrase), '/'), ',');
        $torrent_phrase = $this->shrinkWrapper->escape($torrent_phrase);
        $torrent_phrase = '\'' . $torrent_phrase . '\'';
        $torrent_phrase = strtolower($torrent_phrase);
        $user_ip        = $this->shrinkWrapper->escape($this->ip);
        $user_geo       = $this->shrinkWrapper->escape($this->geo);
        
        
        
        $shrinkWrap = $this->shrinkWrapper; //bind for function
        
        
        $date    = new DateTime();
        $stamp   = $date->getTimestamp();
        $results = $shrinkWrap->query("SELECT SQL_NO_CACHE * FROM terms WHERE phrase = $torrent_phrase LIMIT 1");
		
        if (empty($results)) {
            $insertPhrase  = $shrinkWrap->query("REPLACE INTO terms (phrase, count) VALUES ($torrent_phrase, 1)");
            $insertTracked = $shrinkWrap->query("INSERT INTO tracked_terms (user_ip, term, iso3, time_stamp) VALUES ('$user_ip', $torrent_phrase, '$user_geo', $stamp)");
            
            
        } else {
            $count               = $results[0]["count"] + 1;
            $results[0]["count"] = $count;
            $insertPhrase        = $shrinkWrap->query("REPLACE INTO terms (phrase, count) VALUES ($torrent_phrase, $count)");
            $insertTracked       = $shrinkWrap->query("INSERT INTO tracked_terms (user_ip, term, iso3, time_stamp) VALUES ('$user_ip', $torrent_phrase, '$user_geo', $stamp)");
            http_response_code(200);
            //  echo(json_encode($results[0]));
            
        }
        
    }
    
    
    public function __destruct()
    {
        
    }
}
