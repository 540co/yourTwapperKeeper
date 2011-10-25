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

// Ensure user is an administrator
if (!(in_array($_SESSION['access_token']['screen_name'],$admin_screen_name))) {
$_SESSION['notice'] = "Only administrators are allowed to stop / start archiving processes";
header('Location:index.php');
die;
}
 
// List of archiving scripts
$cmd = $archive_process_array;

// Kill any jobs that might be left hanging
$kill = "kill -9 `ps -ef |grep yourtwapperkeeper|grep -v grep | awk '{print $2}'`";
exec($kill);

// Start and register jobs
$pid = '';
$pids = '';
foreach ( $cmd as $key=>$value ) {
	$job = 'php '.$tk_your_dir.$value;
	$pid = $tk->startProcess($job);
	$pids .= $pid.",";
	mysql_query("update processes set pid = '$pid' where process = '$value'", $db->connection);
}
$pids = substr($pids, 0, -1);


$_SESSION['notice'] = "Twitter archiving processes have been started. (PIDs = $pids)";
header('Location:index.php');
?>
