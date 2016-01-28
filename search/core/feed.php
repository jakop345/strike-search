<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */
 ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
 require_once('shrinkWrap.php');
header('Content-Type: application/json');

        $date = new DateTime();
$dateForamtted = $date->format('Y-m-d H:i:s') ;
         die( json_encode(array(
            "phrase" => "Error Loading",
			"id" => -1,
            "flag" => "us",
            "date" => $dateForamtted
        )));

