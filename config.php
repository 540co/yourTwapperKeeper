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

// LOOK AT README FOR HOW TO CONFIGURE!!!!!!!!!!

/* Host Information */
$tk_your_url = "http://www.yourexample.com/";  												// make sure to include the trailing slash
$tk_your_dir = "/var/www/html/";  															// make sure to include the trailing slash
$youtwapperkeeper_useragent = "Your TwapperKeeper";											// change to whatever you want!

/* Administrators - Twitter screen name(s) who can administer / start / stop archiving */
$admin_screen_name=array('your_twitter_name'); 

/* Users - Twitter screen names that are allowed to use Your Twapper Keeper site - leaving commented means anyone can use site*/
/* $auth_screen_name=array('JohnSmith','SallySue'); */



/* Your Twapper Keeper Twitter Account Information used to query for tweets (this is common for the site) */
$tk_twitter_username = 'TWITTER_USERNAME';               
$tk_twitter_password = 'TWITTER_PASSWORD';
$tk_oauth_token = 'TOKEN';
$tk_oauth_token_secret = 'SECRET'; 

/* Your Twapper Keeper Application Information - setup at http://dev.twitter.com/apps and copy in consumer key and secret */
$tk_oauth_consumer_key = 'KEY';
$tk_oauth_consumer_secret = 'SECRET';

/* MySQL Database Connection Information */                                             
define("DB_SERVER", "localhost");										// change to your hostname
define("DB_USER", "yourdbusername");									// change to your db username
define("DB_PASS", "TBD");												// change to your db password
define("DB_NAME", "yourdbname"); 										// change to your db name

/* Don't mess with this unless you want to get your hands dirty */
$yourtwapperkeeper_version = "version 0.6.0";
$archive_process_array = array('yourtwapperkeeper_crawl.php','yourtwapperkeeper_stream.php','yourtwapperkeeper_stream_process.php');
$twitter_api_sleep_min = 11;
$stream_process_stack_size = 500;
$php_mem_limit = "512M";
ini_set("memory_limit",$php_mem_limit);

class MySQLDB
{
   var $connection;      

 function MySQLDB(){
      $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die(mysql_error());
      mysql_select_db(DB_NAME, $this->connection) or die(mysql_error());
   }

}
$db = new MySQLDB;

?>