<?php
$id = $_POST['id'];
$description = $_POST['description'];
$tags = $_POST['tags'];

// Load important files
session_start();
require_once('config.php');
require_once('function.php');
require_once('twitteroauth.php'); 

// validate information before creating
if (!(isset($_SESSION['access_token']['screen_name']))) {
	$_SESSION['notice'] = 'You must login to edit an archive.';
	header('Location: index.php');
	die;
	}

// validate user is the creator before editing
$archive = $tk->listArchive($id);
if ($archive['results'][0]['user_id'] <> $_SESSION['access_token']['user_id']) {
	$_SESSION['notice'] = 'You did not create this archive, therefore, you cannot edit it.';
	header('Location: index.php');
	die;
	}

// create and redirect
$result = $tk->updateArchive($id,$description, $tags);
$_SESSION['notice'] = $result[0];
header('Location: index.php');

?>