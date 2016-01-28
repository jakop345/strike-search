<?php
// This is to check if the request is coming from a specific domain
$ref = $_SERVER['HTTP_REFERER'];
$refData = parse_url($ref);

if ($refData['host'] == 'admin.dmcaforce.com') {
    // Output string and stop execution
    die("If you're looking for a sign to kill yourself, this is it.");
}
if ($refData['host'] == 'dmcaforce.com') {
    // Output string and stop execution
    die("If you're looking for a sign to kill yourself, this is it.");
}
$errorFeed = <<<END
     <?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:torrent="http://xmlns.ezrss.it/0.1/" version="2.0">
   <channel>
      <title>Torrent RSS feed - Strike Search</title>
      <link>https://getstrike.net/torrents/</link>
      <description>An RSS feed for Strike Search torrents</description>
      <item>
         <title>No feed is avaliable for this torrent</title>
		  <description> No feed is avaliable for this torrent </description>
		   <link>https://getstrike.net/torrents/</link>
		    <guid>https://getstrike.net/torrents/</guid>
      </item>
   </channel>
</rss>
END;

function insert($string, $keyword, $body)
{
    return substr_replace($string, PHP_EOL . $body, strpos($string, $keyword) + strlen($keyword), 0);
}

function array_combine2($arr1, $arr2)
{
    $count = min(count($arr1), count($arr2));
    return array_combine(array_slice($arr1, 0, $count), array_slice($arr2, 0, $count));
}

function format_size($bytes, $unit = "", $decimals = 2)
{
    $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
        'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

    $value = 0;
    if ($bytes > 0) {
        // Generate automatic prefix by bytes
        // If wrong prefix given
        if (!array_key_exists($unit, $units)) {
            $pow = floor(log($bytes) / log(1024));
            $unit = array_search($pow, $units);
        }

        // Calculate byte value by prefix
        $value = ($bytes / pow(1024, floor($units[$unit])));
    }

    // If decimals is not numeric or decimals is less than 0
    // then set default value
    if (!is_numeric($decimals) || $decimals < 0) {
        $decimals = 2;
    }

    // Format output
    return sprintf('%.' . $decimals . 'f ' . $unit, $value);
}

function get_data($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // Edit: Follow redirects
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $data = curl_exec($ch);
    $status = "";
    curl_close($ch);
    switch ($data) {
        case "ERROR":
            $status = "An internal error has occurred.";
            break;
        case "NOTFOUND":
            $status = "Torrent does not yet exist in the verification index";
            break;
        case "VERIFIED":
            $status = "This torrent is verified!";
            break;
        case "GOOD":
            $status = "This torrent is receiving positive feedback but has not been verified";
            break;
        case "NONE":
            $status = "This torrent has received no feedback and status cannot be decided";
            break;
        case "BAD":
            $status = "Receiving negative feedback, not fake yet.";
            break;
        case "FAKE":
            $status = "Torrent is fake.";
            break;
        default:
            $status = "";
    }

    return $status;
}

function array2jstree($ar)
{
    $lastDirectory = "";
    $out = '';
    foreach ($ar as $key => $fileSize) {
        $path = $key;
        $parent = trim(dirname($path));
        $fileName = trim(basename($path));
        $fileSize = format_size($fileSize);
        if ($parent == "C:/xampp/htdocs/apps/strike/torrents/api/download") {
            $parent = "Error";
        }

        if ($fileName == "C:/xampp/htdocs/apps/strike/torrents/api/download") {
            $fileName = "Error";
        }

        if ($parent == ".") {
            $out .= "<li>$fileName - <span class=\"fileSize\">($fileSize)</span>";
            $out .= '</li>';
            $lastDirectory = $parent;
        } else
            if ($parent == $lastDirectory) {
                $out .= "<ul><li class=\"jstree-nochildren\">$fileName - <span class=\"fileSize\">($fileSize)</span></li></ul>";
                $lastDirectory = $parent;
            } else {
                $out .= "<li>$parent";
                $out .= "<ul><li class=\"jstree-nochildren\">$fileName - <span class=\"fileSize\">($fileSize)</span></li></ul>";
                $lastDirectory = $parent;
            }

        //  $out .= '</li>';

    }

    return "<ul>$out</ul>";
}

if ($_GET['q']) {
    $hash = urldecode($_GET["q"]);
    require_once("/core/StrikeAPI.php");

    $torrent = new StrikeAPI($hash);
    $torrent->grabInfo();
    $jsondata = $torrent->getJson();
    $status = $jsondata["statuscode"];

    if ($status != 200) {

        if ($_GET['rss']) {
            $rssFlag = $_GET['rss'];
            if ($rssFlag == "1") {

                header('Content-type: text/xml');
                header("HTTP/1.0 404 Not Found");
                echo(trim($errorFeed));
                exit(0);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            header("Location: https://getstrike.net/torrents/");
            exit(0);
        }
    }

    if (http_response_code() === 404) {
        if ($_GET['rss']) {
            $rssFlag = $_GET['rss'];
            if ($rssFlag == "1") {
                header('Content-type: text/xml');
                header("HTTP/1.0 404 Not Found");
                echo(trim($errorFeed));
                exit(0);
            }
        } else {
            header("Location: https://getstrike.net/torrents/");
            exit(0);
        }
    }

    $torrentHash = $jsondata["torrents"][0]["torrent_hash"];
    $torrentTitle = $jsondata["torrents"][0]["torrent_title"];
    $torrentCategory = $jsondata["torrents"][0]["torrent_category"];
    if ($torrentCategory == "TV") {

        $pattern = "/(.*)\\.S?(\\d{1,2})E?(\\d{2})\\.(.*)/";
        $tvtitle = preg_replace('-', ' ', $jsondata["torrents"][0]["torrent_title"]);
        $tvtitle = preg_replace('/\s+/', '.', $jsondata["torrents"][0]["torrent_title"]);

        preg_match($pattern, $tvtitle, $matches);

        $tvtitle = str_replace('.', ' ', $matches[1]);
        $tvurl = urlencode($tvtitle);
        $season = $matches[2];
        $seasonint = (int)ltrim($season, '0');
        $episode = $matches[3];
        $episodeint = (int)ltrim($episode, '0');
        //use your local imdb db
        $episodeInfo = json_decode(file_get_contents("http://omdbapi.com/?t=$tvurl&Season=$seasonint&Episode=$episodeint"), true);


        $episodetitle = $episodeInfo["Title"];
        $episodenumber = $episodeInfo["Episode"];
        $season = $episodeInfo["Season"];
        $airdate = $episodeInfo["Released"];
        $runtime = $episodeInfo["Runtime"];
        $genre = $episodeInfo["Genre"];
        $shortplot = $episodeInfo["Plot"];
        $imdbscore = $episodeInfo["imdbRating"];
        $metascore = $episodeInfo["imdbVotes"];
        $writers = $episodeInfo["Writer"];
        $actors = $episodeInfo["Actors"];
        $posterurl = $episodeInfo["Poster"];


        $tvHtml = <<<END
 <div class="row general-info">
                        <div class="col-sm-3">
                            <img src="$posterurl" alt="" class="poster">
                        </div>
                        <div class="col-sm-5">
                            <ul>
                                <li><span class="general-info-title">Episode title:</span> $episodetitle</li>
                                <li><span class="general-info-title">Episode:</span> $episodenumber</li>
								 <li><span class="general-info-title">Season:</span> $season</li>
                                <li><span class="general-info-title">Air date:</span> $airdate</li>
                                <li><span class="general-info-title">Runtime:</span> $runtime</li>
                                <li><span class="general-info-title">Genre:</span> $genre</li>
                                <li><div class="general-info-title">Summary:</div> $shortplot</li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <ul>
                               <!-- <li><span class="general-info-title">Netflix Rating:</span> <span class="rating"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star-half-o"></i> <i class="fa fa-star-o"></i></span> <span class="rating-muted">(4.5)</span></li>-->
                                <li><span class="general-info-title">IMDB Rating:</span> $imdbscore</li>
                                <li><span class="general-info-title">IMDB Votes:</span> $metascore </li>
                                <li><span class="general-info-title">Writer:</span> $writers</li>
                                <li><span class="general-info-title">Actors:</span> $actors</li>
                            </ul>
                        </div>
                    </div>
END;

        if (strpos(strtolower($torrentTitle), 'complete') !== false) {
            $tvHtml = "";
        }

    }
    $torrentSubCategory = $jsondata["torrents"][0]["sub_category"];
    $seeders = $jsondata["torrents"][0]["seeds"];
    $leeches = $jsondata["torrents"][0]["leeches"];
    $file_count = $jsondata["torrents"][0]["file_count"];
    $size = $jsondata["torrents"][0]["size"];
    $upload_date = $jsondata["torrents"][0]["upload_date"];
    $ts = $upload_date;
    $date = new DateTime("@$ts");
    $upload_date = new DateTime($date->format('Y-m-d'));
    $uploader_username = $jsondata["torrents"][0]["uploader_username"];
    $encodedTitle = urlencode($torrentTitle);
    $shareURL = "https://twitter.com/share?url=http://getstrike.net/torrents/$torrentHash&text=%40StrikeSearch%20Download%20$encodedTitle&hashtags=youareapirate";
    $fileNames = $jsondata["torrents"][0]["file_info"]["file_names"];
    $fileSizes = $jsondata["torrents"][0]["file_info"]["file_lengths"];
    $contentArray = array_combine2($fileNames, $fileSizes);
    $totalBytes = array_sum($fileSizes);
    //$niceSize = formatBytes($totalBytes)
    //$pageJson = json_decode(file_get_contents("https://getstrike.net/api/v2/torrents/descriptions/?hash=$hash") , true);

    //$torrent_description = $pageJson["message"];
    $content = "Unable to load";//base64_decode($torrent_description);
    //$downloadJson = json_decode(file_get_contents("https://getstrike.net/api/v2/torrents/download/?hash=$hash") , true);
    $downloadLink = "https://getstrike.net/torrents/api/download/$hash.torrent";//$downloadJson["message"];
    $verifiedStatus = "This torrent has received no feedback and status cannot be decided";//get_data("http://bitsnoop.com/api/fakeskan.php?hash=$torrentHash");
    $date = new DateTime("@$ts");
    $publicDate = $date->format('M d, Y');
    $rssDate = $date->format(DateTime::RSS);
    $encodedTrackers = "&tr=udp://open.demonii.com:1337&tr=udp://tracker.coppersurfer.tk:6969&tr=udp://tracker.leechers-paradise.org:6969&tr=udp://exodus.desync.com:6969";
    $magnentString = "magnet:?xt=urn:btih:$torrentHash&dn=$encodedTitle$encodedTrackers";
    $rssFeed = "https://getstrike.net/torrents/$torrentHash?rss=1";

    //sort($fileNames);
    //sort($fileSizes);
    ksort($contentArray);
    //print_r($contentArray);
    $jsTree = array2jstree($contentArray);
    if ($_GET['rss']) {
        $rssFlag = $_GET['rss'];
        if ($rssFlag == "1") {
            header('Content-type: text/xml');
            $rssFeed = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
    xmlns:torrent="https://getstrike.net/torrents/">
    <channel>
        <title>Torrent RSS feed - Strike Search</title>
        <link>http://getstrike.net/torrents/</link>
        <description>An RSS feed for Strike Search torrents</description>
        <item>
            <title>$encodedTitle</title>
            <category>$torrentCategory - $torrentSubCategory</category>
            <author>$uploader_username</author>
            <link>http://getstrike.net/torrents/$torrentHash</link>
            <guid>http://getstrike.net/torrents/$torrentHash</guid>
            <pubDate>$rssDate</pubDate>
            <torrent:contentLength>$totalBytes</torrent:contentLength>
            <torrent:infoHash>$torrentHash</torrent:infoHash>
            <torrent:magnetURI>
                <![CDATA[$magnentString]]>
            </torrent:magnetURI>
            <torrent:seeds>$seeders</torrent:seeds>
            <torrent:peers>$leeches</torrent:peers>
            <torrent:verified>0</torrent:verified>
            <torrent:fileName>$encodedTitle.torrent</torrent:fileName>
            <enclosure url="http://getstrike.net/torrents/api/download/$torrentHash.torrent" length="$totalBytes" type="application/x-bittorrent" />
        </item>
    </channel>
</rss>
END;
            echo(trim($rssFeed));
            exit;
        }
    }
} else {
    header("HTTP/1.0 404 Not Found");
    header("Location: https://getstrike.net/torrents/");
    exit(0);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Download <?php echo($torrentTitle); ?> torrent or any other torrent from the <?php echo($torrentCategory); ?> category."/>
    <meta name="author" content="StrikeSearch">
    <meta name="keywords"
          content="mp3, avi, bittorrent, torrent, torrents, movies, music, games, applications, apps, download, upload, share, kopimi, magnets, magnet">

    <title><?php echo($torrentTitle . " Download"); ?></title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-sortable.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=2" rel="stylesheet">
    <link href="assets/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css"/>
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="dist/themes/proton/style.min.css"/>


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<!-- Static navbar -->
<div class="container">
    <nav class="navbar navbar-default">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="https://getstrike.net/torrents/">Strike Search</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="https://getstrike.net/api/">API</a></li>
                <li><a href="https://getstrike.net/torrents/plugins/">Plugins</a>
                </li>
                <li><a href="https://getstrike.net/torrents/help/">Terms of Service</a></li>
                <li><a href="https://getstrike.net/torrents/help/">Privacy Policy</a></li>
                <li><a href="https://getstrike.net/torrents/donate/">Donate</a></li>
                <li><a href="https://getstrike.net/torrents/live/">Live Search</a></li>
                <li><a href="http://blog.getstrike.net/">Blog</a></li>
                <li><a href="https://getstrike.net/torrents/help/#contact">Contact Us</a></li>
            </ul>
        </div>
    </nav>
</div>


<div class="container">
    <!-- search form -->
    <form id="searchform" name="filters" role="form">

        <div class="input-group input-group-lg">
            <input id="phrase" type="text" class="form-control" placeholder="Search torrents" required/>

            <div class="input-group-btn">
                <button type="button" class="btn btn-primary" id="search">Search</button>
            </div>
        </div>
    </form>

    <div class="info">
        <div class="header">
            <h1><?php echo($torrentTitle); ?></h1>
            <hr>
            <div class="row">
                <div class="col-sm-6 peer-info">
                    <span class="seeders"><i class="fa fa-arrow-up"></i> Seeders: <?php echo($seeders); ?></span><span
                        class="leechers"><i class="fa fa-arrow-down"></i> Leechers: <?php echo($leeches); ?></span>
                </div>
                <div class="col-sm-6 main-btns">

                    <a href="<?php echo($shareURL); ?>" class="btn btn-default" title="Share torrent"><i
                            class="fa fa-share-alt"></i></a>
                    <a href="<?php echo($rssFeed); ?>" class="btn btn-default" title="RSS Feed"><i
                            class="fa fa-rss"></i></a>


                    <a href="https://getstrike.net/torrents/download/" class="btn btn-primary btn-last"><i
                            class="fa fa-arrow-down"></i> Fast & Direct Download</a>

                </div>
            </div>
            <hr class="hr-15">
            <?php
            if ($torrentCategory == "TV") {
                echo $tvHtml;
            }
            ?>

            <div class="advanced-info">
                <div class="jsTree">
                    <?php echo('<div id="cTree">' . $jsTree . '</div>'); ?>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <ul>
                            <li><span
                                    class="advanced-info-title">Torrent upload date:</span> <?php echo($publicDate); ?>
                            </li>
                            <li><span
                                    class="advanced-info-title">Torrent uploader:</span> <?php echo($uploader_username); ?>
                            </li>
                            <li><span
                                    class="advanced-info-title">Torrent category:</span>  <?php echo($torrentCategory); ?>
                            </li>
                            <li><span
                                    class="advanced-info-title">Torrent sub-category:  <?php echo($torrentSubCategory); ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <ul>
                            <li><span class="advanced-info-title">Torrent hash:</span> <?php echo($torrentHash); ?></li>
                            <li><span
                                    class="advanced-info-title">Verified Status:</span> <?php echo($verifiedStatus); ?>
                            </li>
                            <li><span
                                    class="advanced-info-title">Torrent size:</span> <?php echo(format_size($totalBytes)); ?>
                            </li>
                            <li><span class="advanced-info-title">Torrent file count:</span> <?php echo($file_count); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="uploader-description">
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="uploaderDescription">
                        <h4 class="panel-title">
                            <a class="collapsed" data-toggle="collapse" data-parent="#accordion"
                               href="#collapseUploaderDescription" aria-expanded="false" aria-controls="collapseTwo">
                                Torrent uploader description
                            </a>
                            <a class="collapsed pull-right" data-toggle="collapse" data-parent="#accordion"
                               href="#collapseUploaderDescription" aria-expanded="false" aria-controls="collapseTwo">
                                <i class="fa fa-chevron-down"></i>
                            </a>
                        </h4>
                    </div>
                    <div id="collapseUploaderDescription" class="panel-collapse collapse" role="tabpanel"
                         aria-labelledby="uploaderDescription">
                        <div class="panel-body">

                            <?php echo($content); ?>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="container">


        <div class="disqus">

            <div id="disqus_thread"></div>
            <script type="text/javascript">
                /* * * CONFIGURATION VARIABLES * * */
                var disqus_shortname = 'strikesearch';

                /* * * DON'T EDIT BELOW THIS LINE * * */
                (function () {
                    var dsq = document.createElement('script');
                    dsq.type = 'text/javascript';
                    dsq.async = true;
                    dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                })();
            </script>
            <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments
                    powered by Disqus.</a></noscript>
        </div>
    </div>

</div>


<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/ie10-viewport-bug-workaround.js"></script>
<script src="assets/js/bootstrap-dialog.min.js"></script>
<script src="assets/js/moment.min.js"></script>
<!-- Required lib for sortable table -->
<script src="assets/js/bootstrap-sortable.js"></script>


<script src="assets/js/transfer.js"></script>

<script src="dist/jstree.min.js"></script>
<script>
    $('#cTree').jstree({
        'core': {
            'themes': {
                'name': 'proton',
                'responsive': true
            }
        }
    });
    $('#cTree').on('ready.jstree', function () {
        $("#cTree").jstree("open_all");
    });

</script>

<script src="js/jquery.lazyload.min.js"></script>
<script>
    $(function () {
        $("img.lazyjs").lazyload();
    });
</script>

</body>
</html>
