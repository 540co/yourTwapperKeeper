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
//print_r($_SESSION);
require_once('config.php');
require_once('twitteroauth.php'); 


// Build OAuth credentials
$connection = new TwitterOAuth($tk_oauth_consumer_key, $tk_oauth_consumer_secret);
 
// Get Temporary Credentials
$request_token = $connection->getRequestToken($tk_your_url.'callback.php');

// Save Temp Credentials to Session
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

$url = $connection->getAuthorizeURL($token);
header('Location: ' . $url);
 
?>
