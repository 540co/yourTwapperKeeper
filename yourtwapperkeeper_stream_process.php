<?php
// load important files
require_once('config.php');
require_once('function.php');
require_once('twitteroauth_search.php');

// setup values
$pid = getmypid();
$script_key = uniqid();

// process loop
while (TRUE) {
	// lock up some tweets
	$q = "update rawstream set flag = '$script_key' where flag = '-1' limit $stream_process_stack_size";
	echo $q."\n";
	mysql_query($q, $db->connection);  
		
	// get keyword into memory
	$q = "select id,keyword from archives";
	echo $q."\n";
	$r = mysql_query($q, $db->connection);
	$preds = array();
	while ($row = mysql_fetch_assoc($r)) {
		$preds[$row['id']] = $row['keyword'];
	}
	
	// grab the locked up tweets and load into memory
	$q = "select * from rawstream where flag = '$script_key'";
	$r = mysql_query($q, $db->connection);
	echo $q."\n";
	$batch = array();
	while ($row = mysql_fetch_assoc($r)) {
		$batch[] = $row;    
	}
		
	// for each tweet in memory, compare against predicates and insert
	foreach ($batch as $tweet) {
		echo "[".$tweet['id']." - ".$tweet['text']."]\n";        
        foreach ($preds as $ztable=>$keyword) {
        	if (stristr($tweet['text'],$keyword) == TRUE) {
        		echo " vs. $keyword = insert\n";
        		$q_insert = "insert into z_$ztable values ('twitter-stream','".$tweet['text']."','".$tweet['to_user_id']."','".$tweet['from_user']."','".$tweet['id']."','".$tweet['from_user_id']."','".$tweet['iso_language_code']."','".$tweet['source']."','".$tweet['profile_image_url']."','".$tweet['geo_type']."','".$tweet['geo_coordinates_0']."','".$tweet['geo_coordinate_1']."','".$tweet['created_at']."','".$tweet['time']."')";
        		$r_insert = mysql_query($q_insert, $db->connection);
        		} else {
        		echo " vs. $keyword = not found\n";
        		}
        	}
        echo "---------------\n";
        }
    
    // delete tweets in flag
    $q = "delete from rawstream where flag = '$script_key'";
    echo $q."\n";
    mysql_query($q, $db->connection);
    
    // update counts
    foreach ($preds as $ztable=>$keyword) {
    	$q_count = "select count(id) from z_$ztable";
    	$r_count = mysql_query($q_count, $db->connection);
    	$r_count = mysql_fetch_assoc($r_count);
    	$q_update = "update archives set count = '".$r_count['count(id)']."' where id = '$ztable'";
    	echo $q_update."\n";
    	mysql_query($q_update, $db->connection);
    	
    }
    
	// update pid and last_ping in process table
	mysql_query("update processes set last_ping = '".time()."' where pid = '$pid'", $db->connection);
	echo "update pid\n";
}









?>
