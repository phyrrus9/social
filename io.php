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
		case "deletepost":
			dodeletepost($_SESSION['userinfo']['uid'], $_POST['postpid']);
			break;
		case "editpost":
			doeditpost($_SESSION['userinfo']['uid'], $_POST['postpid']);
			break;
		case "publishedit":
			dopublishedit($_SESSION['userinfo']['uid'], $_POST['postpid'], $_POST['postmsg']);
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

	function dodeletepost($uid, $pid)
	{
		delete_post($uid, $pid);
		do_redirect("index.php");
	}

	function doeditpost($uid, $pid)
	{
		if (can_edit($uid, $pid))
			printeditform($pid);
		
	}

	function dopublishedit($uid, $pid, $msg)
	{
		if (can_edit($uid, $pid))
		{
			$edittime = time();
			$query = "UPDATE posts SET " .
					 "time='$edittime', ".
					 "text='$msg' "      .
					 "WHERE pid='$pid';" ;
			$conn = common_connect();
			sql_query($conn, $query);
			sql_disconnect($conn);
		}
		do_redirect("./index.php#pid" . $pid);
	}

?>