<?php
require_once('Phirehose.php');
require_once('config.php');
require_once('function.php');


class DynamicTrackConsumer extends Phirehose
{ 
  
  public function enqueueStatus($status)
  {
  	global $db;
   	$status = json_decode($status); 
    $status = get_object_vars($status);
    
   
    if ($status['id'] <> null) {
        
        $values_array = array();
       
        $geo = get_object_vars($status['geo']); 
        $user= get_object_vars($status['user']);
        
        $values_array[] = "-1";                                     // processed_flag [-1 = waiting to be processed]
        $values_array[] = $status['text'];                          // text
        $values_array[] = $status['in_reply_to_user_id'];           // to_user_id
        $values_array[] = $user['screen_name'];                     // from_user 
        $values_array[] = $status['id'];                            // id -> unique id of tweet 
        $values_array[] = $user['id'];                              // from_user_id
        $values_array[] = $user['lang'];                            // iso_language_code
        $values_array[] = $status['source'];                        // source
        $values_array[] = $user['profile_image_url'];               // profile_img_url
        $values_array[] = $geo['type'];                             // geo_type 
        $values_array[] = $geo['coordinates'][0];                   // geo_coordinates_0
        $values_array[] = $geo['coordinates'][1];                   // geo_coordinates_1
        $values_array[] = $status['created_at'];                    // created_at
        $values_array[] = strtotime($status['created_at']);         // time
        
        $values = '';
        foreach ($values_array as $insert_value) {
        $values .= "'$insert_value',";
        }
        $values = substr($values,0,-1);   
        
        $q = "insert into rawstream values($values)";    
        $result = mysql_query($q, $db->connection);
    }
  }
 
  public function checkFilterPredicates()
  {
    global $db;
   
    $q = "select id,keyword from archives";
    $r = mysql_query($q, $db->connection);
    
    $track = array();
  	while ($row = mysql_fetch_assoc($r)) {
  	 	$track[] = $row['keyword'];
  	 	$track_matrix['id'] = $row['keyword'];
  	}
  	$this->setTrack($track); 
  	
  	// update pid and last_ping in process table
  	$pid = getmypid();
	mysql_query("update processes set last_ping = '".time()."' where pid = '$pid'", $db->connection);
	echo "update pid\n";
  }
  
}

// Start streaming
$sc = new DynamicTrackConsumer($tk_twitter_username, $tk_twitter_password, Phirehose::METHOD_FILTER);
$sc->consume();