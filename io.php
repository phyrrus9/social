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
			$silent = false;
			$unflag = false;
			if (isset($_POST['silent']))
				if (!strcmp($_POST['silent'], "true"))
					$silent = true;
			if (isset($_POST['unflag']))
				if (!strcmp($_POST['unflag'], "true"))
					$unflag = true;
			dopublishedit($_SESSION['userinfo']['uid'], $_POST['postpid'],
						  $_POST['postmsg'], $silent, $unflag);
			break;
		case "flagpost":
			doflagpost($_POST['postpid']);
			break;
		default:
		die("What sort of trickery is this?");
	}

	function dounflag($pid)
	{
		if (check_perm(ACCESS_ADMIN_FLAGS))
		{
			$conn = common_connect();
			sql_query($conn, "UPDATE posts SET flag='0' WHERE pid='$pid';");
			sql_disconnect($conn);
			$_SESSION['ADMIN_RESULT_BOX'] = "Post $pid unflagged";
		}
		else
			$_SESSION['ADMIN_RESULT_BOX'] = "You are not allowed to manage flags";
	}

	function docreatecomment($pid)
	{
		printcommentform($pid);
		//there should probably be more here
	}

	function doflagpost($pid)
	{
		$conn = common_connect();
		sql_query($conn, "UPDATE posts SET flag='1' WHERE pid='$pid';");
		sql_disconnect($conn);
		do_redirect("index.php#pid" . $pid);
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

	function dopublishedit($uid, $pid, $msg, $silent, $unflag)
	{
		if (can_edit($uid, $pid))
		{
			$edittime = time();
			$query = "UPDATE posts SET " .
 					 "time='$edittime', " .
					 "text='".nl2br($msg)."' " .
					 "WHERE pid='$pid';" ;
			if ($silent == true)
				$query = "UPDATE posts SET " .
					 	 "text='".nl2br($msg)."' " .
					 	 "WHERE pid='$pid';" ;
			$conn = common_connect();
			sql_query($conn, $query);
			sql_disconnect($conn);
			if ($unflag == true)
			{
				dounflag($pid);
				do_redirect("./admin.php");
			}
		}
		do_redirect("./index.php#pid" . $pid);
	}

?>