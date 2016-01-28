<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */

if (!empty($_POST["query"])) {
	
    $phrase = urldecode($_POST["query"]);
	
	$testPhrase = str_replace(' ', '', $phrase);
	if (strlen($testPhrase) < 4) {
		die('<div class="enter-search-phrase">Your query cannot be less than 4 characters</div>');
 
	}
    require_once("TorrentSearch.php");
    $torrent = new TorrentSearch($phrase);
    $torrent->getTorrents();
} else {
    die('<div class="enter-search-phrase">Please enter a search phrase</div>');
}

