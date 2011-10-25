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

$export_file = $archiveInfo['results'][0]['keyword'].".xls";
ob_end_clean();
ini_set('zlib.output_compression','Off');
   
header('Pragma: public');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");                  // Date in the past   
header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
header ("Pragma: no-cache");
header("Expires: 0");header('Content-Transfer-Encoding: none');
    header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
    header("Content-type: application/x-msexcel");                    // This should work for the rest
    header('Content-Disposition: attachment; filename="'.basename($export_file).'"'); 

echo "<table>";
echo "<tr>";
echo "<th>ARCHIVESOURCE</th>";
echo "<th>TEXT</th>";
echo "<th>TO_USER_ID</th>";
echo "<th>FROM_USER</th>";
echo "<th>ID</th>";
echo "<th>FROM_USER_ID</th>";
echo "<th>ISO_LANGUAGE_CODE</th>";
echo "<th>SOURCE</th>";
echo "<th>PROFILE_IMG_URL</th>";
echo "<th>GEO_TYPE</th>";
echo "<th>GEO_COORDINATES_0</th>";
echo "<th>GEO_COORDINATES_1</th>";
echo "<th>CREATED_AT</th>";
echo "<th>TIME</th>";
echo "</tr>";

foreach ($archiveTweets as $key=>$value) {

	echo "<tr>";
	foreach ($value as $cols) {
		echo "<td>$cols</td>";}
	echo "</tr>";

} 

echo "</table>";
?>
