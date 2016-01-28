<?php
/**
 * Created by Andrew sampson
 * User: Andrew
 * Date: 5/19/14
 * Time: 5:26 AM
 */


if (!empty($_GET["query"])) {
    $phrase = urldecode($_GET["query"]);
    require_once("TorrentSearch.php");
    $torrent = new TorrentSearch($phrase);
    $torrent->getTorrents();
} else {
    die('Please enter a search phrase..');
}

