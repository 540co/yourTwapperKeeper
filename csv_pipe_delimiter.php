<?php

// Load important files
session_start();
require_once('config.php');
require_once('function.php');
require_once('twitteroauth.php');

$id = $_GET['id'];
$archiveInfo = $tk->listArchive($id);
if ($archiveInfo['count'] <> 1 || (!(isset($_GET['id'])))) {
	$_SESSION['notice'] = "Archive does not exist.";
	header('Location: index.php');
	}

// set link
$link = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
// set default limit
if ($_GET['l'] == '') {$limit = 10;} else {$limit = $_GET['l'];}
if ($_GET['o'] == '') {$orderby = 'd';} else {$orderby = $_GET['o'];} 

// set times
if ($_GET['sm'] <> '' && $_GET['sd'] <> '' && $_GET['sy'] <> '') {
    $start_time = strtotime($_GET['sm']."/".$_GET['sd']."/".$_GET['sy']);}
if ($_GET['em'] <> '' && $_GET['ed'] <> '' && $_GET['ey'] <> '') {
    $end_time = strtotime($_GET['em']."/".$_GET['ed']."/".$_GET['ey']);}
    
// Get tweets
if ($start_time <> '' || $end_time <> '') {
$archiveTweets = $tk->getTweets($_GET['id'],$start_time,$end_time,$limit,$orderby,$_GET['nort'],$_GET['from_user'],$_GET['text'],$_GET['lang'],$_GET['max_id'],$_GET['since_id'],$_GET['offset'],$_GET['lat'],$_GET['long'],$_GET['rad'],$_GET['debug']);
} else {
$archiveTweets = $tk->getTweets($_GET['id'],null,null,$limit,$orderby,$_GET['nort'],$_GET['from_user'],$_GET['text'],$_GET['lang'],$_GET['max_id'],$_GET['since_id'],$_GET['offset'],$_GET['lat'],$_GET['long'],$_GET['rad'],$_GET['debug']);
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=".$archiveInfo['results'][0]['keyword'].".csv");
header("Pragma: no-cache");
header("Expires: 0");

echo "text|to_user_id|from_user|id|from_user_id|iso_language_code|source|profile_img_url|geo_type|geo_coordinates_0|geo_coordinates_1|created_at|time";

foreach ($archiveTweets as $key=>$value) {
	array_shift($value);
	$k = 0;
	echo "\n";
	foreach ($value as $cols) {
		if($k>0){
			echo "|";
		}
		echo preg_replace( "/\r|\n/", "", $cols );
		$k++;
	}
}

?>
