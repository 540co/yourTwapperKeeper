<?php
// load important files
require_once('config.php');
require_once('function.php');
require_once('twitteroauth_search.php');

// setup values
$pid = getmypid();
$sleep = $twitter_api_sleep_min;
$count = 0;

// Setup connection
$connection = new TwitterOAuth($tk_oauth_consumer_key, $tk_oauth_consumer_secret, $tk_oauth_token, $tk_oauth_token_secret); 
$connection->useragent = $youtwapperkeeper_useragent; 
 
while (TRUE) {
	 // Query for archives 
	 $q_archives = "select * from archives";
	 $r_archives = mysql_query($q_archives, $db->connection); 
	 
	 while ($row_archives = mysql_fetch_assoc($r_archives)) {
	 
	 // sleep for rate limiting
	 echo "sleep = $sleep\n";
	 sleep($sleep);
	 
	 echo $row_archives['id']." - ".$row_archives['keyword']."\n";
	  
	 // Loop for 15 pages
	 for ($page_counter = 1; $page_counter <=15 ; $page_counter = $page_counter + 1) {
	 		 	
	 	$search = $connection->get('search.twitter.com/search', array('q' => $row_archives['keyword'], 'rpp' => 100, 'page' => $page_counter));
	 	
	 	$searchresult = get_object_vars($search);
	 	$count = count($searchresult['results']);
	
		// parse results
		foreach ($searchresult['results'] as $key=>$value) {
			$value = get_object_vars($value);
        	    		
    		// extract data
    		extract($value,EXTR_PREFIX_ALL,'temp');
    		
        	// extract geo information
        	$geo = get_object_vars($temp_geo);
        	$geo_type = $geo['type'];
        	$geo_coordinates_0 = $geo['coordinates'][0];
        	$geo_coordinates_1 = $geo['coordinates'][1];
    		
    		// duplicate record check and insert into proper cache table if not a duplicate
        	$q_check = "select id from z_".$row_archives['id']." where id = '".$value['id']."'";
        	$result_check = mysql_query($q_check, $db->connection);
        
        if (mysql_numrows($result_check)==0) {
        	$q = "insert into z_".$row_archives['id']." values ('twitter-search','".mysql_real_escape_string($temp_text)."','$temp_to_user_id','$temp_from_user','$temp_id','$temp_from_user_id','$temp_iso_language_code','$temp_source','$temp_profile_image_url','$geo_type','$geo_coordinates_0','$geo_coordinates_1','$temp_created_at','".strtotime($temp_created_at)."')";
        	mysql_query($q, $db->connection);
        	echo "[".$row['id']."-".$row['keyword']."] $page_counter - $temp_id - insert\n";
        	} else {echo "$page_counter - $temp_id - duplicate\n";}
        }
       
   	// If count for page is less than 100, break since there is no reason to keep going
    if ($count < 100) {
        break;
        }
    
    }
    
    // adjust sleep if being rate limited
   	$rate_check = $connection->get('api.twitter.com/1/account/rate_limit_status');
   	echo "rate left = ".$rate_check->remaining_hits."\n";
   	if ($rate_check->remaining_hits < 1) {
   		$sleep = $sleep * 2;
   	} else {
   		if ($sleep > $twitter_api_sleep_min) {
   		$sleep = $sleep / 2;
   		}
   	}

	// update counts
	$q_count_total = "select count(id) from z_".$row_archives['id'];
	$r_count_total = mysql_query($q_count_total, $db->connection);  
	$r_count_total = mysql_fetch_assoc($r_count_total);
	$q_update_count_total = "update archives set count = '".$r_count_total['count(id)']."' where id = '".$row_archives['id']."'";
	mysql_query($q_update_count_total,$db->connection);
	echo "update count\n";

	// update pid and last_ping in process table
	mysql_query("update processes set last_ping = '".time()."' where pid = '$pid'", $db->connection);
	echo "update pid\n";
	}




}





?>
