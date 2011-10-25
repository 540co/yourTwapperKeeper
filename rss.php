<?php
/*
yourTwapperKeeper - Twitter Archiving Application - http://your.twapperkeeper.com
Copyright (c) 2010 John O'Brien III - http://www.linkedin.com/in/jobrieniii

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

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

                                                                                         
// RSS DATA
header('Content-type: text/xml');
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:geo='http://www.w3.org/2003/01/geo/wgs84_pos#' xmlns:dc='http://purl.org/dc/elements/1.1/'>";
	
echo "<channel>";
echo "<title>TwapperKeeper Archive - ".$archiveInfo['results'][0]['keyword']." - ".$archiveInfo['results'][0]['description']."</title>";
echo "<link>".htmlspecialchars($link)."</link>";
echo "<description>TwapperKeeper Archive - ".$archiveInfo['results'][0]['keyword']." - ".$archiveInfo['results'][0]['description']."</description>";
echo "<language>en-us</language>";           
echo "<docs>$tk_your_url</docs>";
echo "<generator>PHP</generator>";

foreach ($archiveTweets as $key=>$value) {
    $title = $value['id'];
    $text = $value['text'];
    
    $text = preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $text);
    $text = htmlspecialchars($text);
    
 	$description = $value['from_user'].": ".$text." - ".$value['created_at']." - tweet id ".$value['id'];
    
    $guid = "tag:twapper:feed:".$archiveInfo['keyword'].$value['id'];
    $pubDate = strtotime($value['created_at']);
     
    echo "<item>";
    echo "<title>$description</title>";
    echo "<description>$description</description>";
    echo "<pubDate>".date(DATE_RSS,$pubDate)."</pubDate>";
    if ($value['geo_coordinates_0'] <> 0 and $value['geo_coordinates_1'] <> 0) {
        echo "<geo:lat>".$value['geo_coordinates_0']."</geo:lat>";
        echo "<geo:long>".$value['geo_coordinates_1']."</geo:long>";
    }
    echo "<guid isPermaLink='false'>$guid</guid>";
    echo "</item>";
} 

echo "</channel>";
echo "</rss>";

?>
