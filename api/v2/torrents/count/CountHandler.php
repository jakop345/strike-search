<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:22 AM
 */


require_once('../core/shrinkWrap.php');

class CountHandler
{
    
    
    private $shrinkWrapper;
    
    public function __construct()
    {
        $this->shrinkWrapper = new shrinkWrap('localhost', '', '', 'strike_search'); //connects to the db
        
    }
    
    
    public function getTotalTorrents()
    {
        
        
        $shrinkWrap = $this->shrinkWrapper;
        
        
        $results = $shrinkWrap->query("SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'torrents';");
        if (empty($results)) {
            http_response_code(500);
            $error = '{"statuscode":500,"Internal server error"}';
            die($error);
        } else {
            
            $total = $results[0]["TABLE_ROWS"];
            $error = "{\"statuscode\":200,\"message\":$total}";
            die($error);
        }
        
    }
    
    
    public function __destruct()
    {
        
    }
}