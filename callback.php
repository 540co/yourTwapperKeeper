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
require_once('twitteroauth.php'); 

// If the oauth_token is old redirect to clearsessions
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
  header('Location: ./clearsessions.php');
}

// Create TwitteroAuth object with app key/secret and token key/secret from default phase
$connection = new TwitterOAuth($tk_oauth_consumer_key, $tk_oauth_consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// Request access tokens from twitter
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

// If there is an auth_screen_name list in config, check to see if in auth list
if (isset($auth_screen_name)) {
	if (!(in_array($access_token['screen_name'],$auth_screen_name)))
		{
			$_SESSION['notice'] = 'Twitter screen name '.$access_token['screen_name'].' is not authorized to use YourTwapperKeeper.';
  			header('Location: ./clearsessions.php?notice=Twitter screen name is not authorized to use YourTwapperKeeper.');
  			die;
		}
	} 

// Set the access token 
$_SESSION['access_token'] = $access_token;

// Remove no longer needed request tokens
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

// If HTTP response is 200 continue otherwise send to connect page to retry 
if (200 == $connection->http_code) {
  $_SESSION['status'] = 'verified';
  header('Location: ./index.php');
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  header('Location: ./clearsessions.php');
}