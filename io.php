<?php

	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();

	switch ($_POST['action'])
	{
		case "createcomment":
			docreatecomment($_POST['commentpid']);
		break;
		case "postcomment":
			dopostcomment($_POST['commentpid']);
		break;
		default:
		die("What sort of trickery is this?");
	}

	function docreatecomment($pid)
	{
		printcommentform($pid);
		//there should probably be more here
	}

	function dopostcomment($pid)
	{
		create_post($_SESSION['userinfo']['uid'], $_POST['commenttext'], $_POST['commentpid']) or
			die("could not create comment");
		do_redirect("index.php#pid" . $pid);
	}

?>