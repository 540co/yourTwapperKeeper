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

class YourTwapperKeeper
{

// sanitize data
function sanitize($input) {
        if(is_array($input)) {
            foreach($input as $k=>$i){
            $output[$k]=$this->sanitize($i);
            }
        }
        else {
            if(get_magic_quotes_gpc()){
                $input=stripslashes($input);
            }
        $output=mysql_real_escape_string($input);
    }   
    return $output;
    }

// list archives
function listArchive($id=false,$keyword=false,$description=false,$tags=false,$screen_name=false,$debug=false) {
	global $db;
	
	$q = "select * from archives where 1";
	
	if ($id) {
	$q .= " and id = '$id'";
	}
	
	if ($keyword) {
	$q .= " and keyword like '%$keyword%'";
	}
	
	if ($description) {
	$q .= " and description like '%$description%'";
	}
	
	if ($tags) {
	$q .= " and tags like '%$tags%";
	}
	
	if ($screen_name) {
	$q .= " and screen_name like '%$screen_name%";
	}
	
	
	$r = mysql_query($q, $db->connection);
	
	$count = 0;
	while ($row = mysql_fetch_assoc($r)){
		$count++;
		$response['results'][] = $row;
	}
	
	$response['count'] = $count;
	
	return $response;
	}
    
// create archive
function createArchive($keyword,$description,$tags,$screen_name,$user_id,$debug=false) {
	global $db;
	$q = "select * from archives where keyword = '$keyword'";
	$r = mysql_query($q, $db->connection);
	if (mysql_num_rows($r) > 0) {
	$response[0] = "Archive for that keyword / hashtag already exists.";
	return($response);
	}
	
	if (strlen($keyword) < 1 || strlen($keyword) > 30) {
	$response[0] = "Keyword / hashtag cannot be blank";
	return($response);
	}
	
	if (strlen($keyword) > 30) {
	$response[0] = "Keyword / hashtag must be less than 30 characters.";
	return($response);
	}
	
	$q = "insert into archives values ('','$keyword','$description','$tags','$screen_name','$user_id','','".time()."')";
	$r = mysql_query($q, $db->connection);
	$lastid = mysql_insert_id();
		
    $create_table = "CREATE TABLE IF NOT EXISTS `z_$lastid` (
        `archivesource` varchar(100) NOT NULL,
        `text` varchar(1000) NOT NULL,
        `to_user_id` varchar(100) NOT NULL,
        `from_user` varchar(100) NOT NULL,
        `id` varchar(100) NOT NULL,
        `from_user_id` varchar(100) NOT NULL,
        `iso_language_code` varchar(10) NOT NULL,
        `source` varchar(250) NOT NULL,
        `profile_image_url` varchar(250) NOT NULL,
        `geo_type` varchar(30) NOT NULL,
        `geo_coordinates_0` double NOT NULL,
        `geo_coordinates_1` double NOT NULL,
        `created_at` varchar(50) NOT NULL,
        `time` int(11) NOT NULL,
        FULLTEXT `full` (`text`),
        INDEX `source` (`from_user`),
        INDEX `from_user` (`from_user`),
        INDEX `iso_language_code` (`iso_language_code`),
        INDEX `geo_type` (`geo_type`),
        INDEX `id` (`id`),
        INDEX `time` (`time`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
       
    $r = mysql_query($create_table, $db->connection);
       
    $response[0] = "Archive has been created.";
	return($response);
	}

// get tweets
function getTweets($id,$start=false,$end=false,$limit=false,$orderby=false,$nort=false,$from_user=false,$text=false,$lang=false,$max_id=false,$since_id=false,$offset=false,$lat=false,$long=false,$rad=false,$debug=false) {
	global $db;
	
	$response = array();
    $type = $this->sanitize($type);
    $name = $this->sanitize($name);
    $start = $this->sanitize($start);
    $end = $this->sanitize($end);
    $limit= $this->sanitize($limit);  
    $orderby= $this->sanitize($orderby);
    $nort= $this->sanitize($nort);
    $from_user= $this->sanitize($from_user); 
    $text= $this->sanitize($text);
    $lang= $this->sanitize($lang);
    $offset = $this->sanitize($offset);
    $max_id = $this->sanitize($max_id);  
    $since_id = $this->sanitize($since_id);   
    $lat = $this->sanitize($lat);
    $long = $this->sanitize($long);
    $rad = $this->sanitize($rad);  
	
	$q = "select * from z_".$id." where 1";
	
	// build param query
    $qparam = '';
    
    if ($start > 0) 	{$qparam .= " and time > $start";}
    
    if ($end > 0) 		{$qparam .= " and time < $end";}
        
    if ($nort == 1) 	{$qparam .= " and text not like 'RT%'";}
    
    if ($from_user) 	{$qparam .= " and from_user = '$from_user'";}
    
    if ($text) 			{$qparam .= " and text like '%$text%'";}
    
    if ($lang) 			{$qparam .= " and iso_language_code='$lang'";}
    
    if ($since_id) 		{$qparam .= " and id >= $since_id";}
    
    if ($max_id) 		{$qparam .= " and id <= $max_id";}
        
   	if ($lat OR $long OR $rad) {
        	
        				$R = 6371;  // earth's radius, km
        	
        	        	$maxLat = $lat + rad2deg($rad/$R);
        				$minLat = $lat - rad2deg($rad/$R);
        	
        	        	$maxLon = $lon + rad2deg($rad/$R/cos(deg2rad($lat)));
        				$minLon = $lon - rad2deg($rad/$R/cos(deg2rad($lat)));
        	
        				$qparam .= " and geo_coordinates_0 > $minLat and geo_coordinates_0 < $maxLat and geo_coordinates_1 > $minLon and geo_coordinates_1 < $maxLon";
        				}

	if ($orderby == "a") {$qparam .= " order by time asc";} else {$qparam .= " order by time desc";}
	
	if ($limit) 		 {$qparam .= " limit $limit";}
	
	$query = $q.$qparam;
	
    $r = mysql_query($query, $db->connection);
	
	$response = array();
	while ($row = mysql_fetch_assoc($r)) {
		$response[] = $row;
	}
	return $response;
}

// delete archive
function deleteArchive($id) {
	global $db;
	$q = "delete from archives where id = '$id'";
	$r = mysql_query($q, $db->connection);
	
	$q = "drop table if exists z_$id";
	$r = mysql_query($q, $db->connection);
	
	$response[0] = "Archive has been deleted.";
	return($response);
	
	
	}

// update archive
function updateArchive($id,$description,$tags) {
	global $db;
	$q = "update archives set description = '$description' where id = '$id'";
	$r = mysql_query($q, $db->connection);
	$q = "update archives set tags = '$tags' where id = '$id'";
	$r = mysql_query($q, $db->connection);
	$response[0] = "Archive has updated.";
	return($response);
	}
	
// check status of archiving processes	
function statusArchiving($process_array) {
	global $db;
	// If PIDs > 0 - we are considered running
	$running = TRUE;
	$pids = '';
	$shouldBeRunning= 1;
	foreach ($process_array as $key=>$value) {
		$q = "select * from processes where process = '$value'";
		$r = mysql_query($q, $db->connection);
		$r = mysql_fetch_assoc($r);
		$pid = $r['pid'];
		exec("ps $pid", $PROC);
		if ( count($PROC) < 2 ) {
			$running=FALSE;
		}
		$pids .= $pid.",";
		if ($pid == 0) {
			$running = FALSE;
			$shouldBeRunning=FALSE;
		}
	}
	$pids = substr($pids, 0, -1);
	
	$result = array();
	if ($running == FALSE) {
	$result[0] = FALSE;
	if ( $shouldBeRunning == 1 ) {
		$result[1] = "<div style='color:red'>Archiving processes have died.  (PIDS = $pids) </div>";
	} else {
		$result[1] = "<div style='color:red'>Archiving processes are NOT running</div>";
	}
	
	} else {
	$result[0] = TRUE;
	$result[1] = "<div style='color:green'>Archiving processes are running (PIDS = $pids)</div>";
	}	
	
	return($result);
}
    

// kill archiving process
function killProcess($pid) {
    $command = 'kill -9 '.$pid;
    exec($command);
}

// start archiving process
function startProcess($cmd) {
	$command = "$cmd > /dev/null 2>&1 & echo $!";
    exec($command ,$op);
    $pid = (int)$op[0];
    return ($pid);
}	



}



$tk = new YourTwapperKeeper;


?>
