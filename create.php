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

// validate information before creating
if (!(isset($_SESSION['access_token']['screen_name']))) {
	$_SESSION['notice'] = 'You must login to create an archive.';
	header('Location: index.php');
	die;
	}

// create and redirect
$result = $tk->createArchive($_POST['keyword'],$_POST['description'],$_POST['tags'],$_SESSION['access_token']['screen_name'],$_SESSION['access_token']['user_id']);
$_SESSION['notice'] = $result[0];
header('Location: index.php');

?>