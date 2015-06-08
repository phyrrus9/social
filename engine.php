<?php
	require_once('config.php');
	require_once('sql.php');
	require_once('sortalgo.php');
	require_once('permissions.php');

	function ispostflagged($pid)
	{
		$conn = common_connect();
		$res = sql_dquery($conn, "SELECT * FROM posts WHERE pid='$pid';")[0];
		if ($res['flag'] == 1)
			return true;
		sql_disconnect($conn);
		return false;
	}

	function br2nl($string)
	{ return preg_replace('/\<br(\s*)?\/?\>/i', "", $string); } 

	function getuidbyusername($name)
	{
		$conn = common_connect();
		$row = sql_dquery($conn, "SELECT * FROM users WHERE username='$name';");
		if ($row == null) return null;
		$ret = array(
				'uid'  => $row[0]['uid'],
				'user' => $row[0]['username'],
				'name' => $row[0]['name'],
				'level'=> $row[0]['permission']
			);
		return $ret['uid'];
	}

	function getuserinfo($uid)
	{
		$conn = common_connect();
		$row = sql_dquery($conn, "SELECT * FROM users WHERE uid='$uid';")[0];
		$ret = array(
				'uid'  => $row['uid'],
				'user' => $row['username'],
				'name' => $row['name'],
				'level'=> $row['permission']
			);
		return $ret;
	}

	function do_redirect($loc)
	{
		echo("<script>window.location.assign(\"$loc\");</script>");
	}

	function checklogin() //start a session, return true if logged in, or die/error page
	{
		session_start();
		if (isset($_SESSION['userinfo']))
			if (isset($_SESSION['userinfo']['uid']))
				return true;
		do_redirect("login.php");
		die("redirect fail, <a href=\"login.php\">Log In</a>");
		return false; //because why not add this
	}

	function common_connect() { return sql_connect($GLOBALS['SQL_SERVER'], $GLOBALS['SQL_USER'], $GLOBALS['SQL_PASS'], $GLOBALS['SQL_DB']); }

	function delete_post($uid, $pid)
	{
		$postinfo = getpostinfo($pid);
		if ($postinfo == null) return false; //does not exist
		if ($postinfo['uid'] != $uid and $uid != 1) return false; //not permitted
		$conn = common_connect();
		$children = sql_dquery($conn, "SELECT * FROM posts WHERE parent='$pid';");
		foreach($children as $child)
			delete_post(1, $child['pid']); //keep things clean
		sql_query($conn, "DELETE FROM posts WHERE pid='$pid';");
		return sql_return($conn, true);
	}

	function create_post($uid, $msg, $pid = 0)
	{
		$msg = nl2br($msg);
		if (strlen($msg) > 256)  {
			echo("too long");
			return false; //message too long
		}
		if (!isfriendswith($uid, getpostinfo($pid)['uid'])) {
			echo("not permitted");
			return false; //not permitted
		}
		$conn = common_connect();
		$posttime = time();
		sql_query($conn, "INSERT INTO posts(time, parent, uid, text) " .
				 		 "VALUES('$posttime', '$pid', '$uid', '$msg');");
		sql_disconnect($conn);
		return true;
		//sql_return($conn, true);
		
	}

	function getpostinfo($pid)
	{
		$conn = common_connect();
		$ret = sql_dquery($conn, "SELECT * FROM posts WHERE pid='$pid';");
		sql_disconnect($conn);
		if ($ret == null)
			return null;
		return $ret[0];
	}

	function isfriendswith($uid, $me = null) //returns true if friends, false if not
	{
		if ($me == null)
			$me = $_SESSION['userinfo']['uid'];
		if ($me == 1) return true; //admin clause
		$conn = common_connect();
		$query = "SELECT * FROM friends WHERE owner='$uid';";
		$info = sql_dquery($conn, $query);
		foreach($info as $row)
		{
			if ($row['friend'] == $me)
			{
				sql_disconnect($conn);
				return true;
			}
		}
		sql_disconnect($conn);
		return false;
	}

	function ismutualfriendswith($uid1, $uid2)
		{ return isfriendswith($uid1, $uid2) and isfriendswith($uid2, $uid1); }

	function get_friend_requests($uid)
	{
		$conn = common_connect();
		$info = sql_dquery($conn, "select * from friends where owner!='$uid' and friend='$uid';");
		$ret = array();
		foreach($info as $row)
		{
			$ins = array();
			$ins['UID'] = $row['owner'];
			if (isfriendswith($uid, $ins['UID']))
				continue;
			array_push($ret, $ins);
		}
		for ($i = 0; $i < count($ret); $i = $i + 1)
		{
			$get_uid = $ret[$i]['UID'];
			$res = sql_query($conn, "SELECT name FROM users WHERE uid='$get_uid';");
			$row = mysql_fetch_assoc($res);
			$ret[$i]['NAME'] = $row['name'];
		}
		sql_disconnect($conn);
		return $ret;
	}

	function get_friends($uid, $mutual = true) //returns an array of the user's friends (as arrays with the following)
		//UID => uid of friend
		//NAME => name of friend
	{
		$conn = common_connect();
		//die("select * from friends where owner='$uid';");
		$info = sql_dquery($conn, "select * from friends where owner='$uid';");
		$ret = array();
		foreach($info as $row)
		{
			$ins = array();
			$ins['UID'] = $row['friend']; //get the first
			if ($mutual == false and ismutualfriendswith($uid, $ins['UID']))
					continue;
			array_push($ret, $ins);
		}
		for ($i = 0; $i < count($ret); $i = $i + 1)
		{
			$get_uid = $ret[$i]['UID'];
			$res = sql_query($conn, "SELECT name FROM users WHERE uid='$get_uid';");
			$row = mysql_fetch_assoc($res);
			$ret[$i]['NAME'] = $row['name'];
		}
		//return sql_return($conn, $ret);
		sql_disconnect($conn);
		return $ret;
	}

	function get_specific_timeline($uid, $pid = 0, $limit = 50, $include = false) //returns array of posts in the following format:
		//PID = post id
		//UID = creator
		//TIME = UNIX time of posting
		//TEXT = message content
		//NAME = display name of poster
		//REPLIES = null if no comments, timeline array if comments
	{
		$commentlim = $limit / 10;
		$conn = common_connect();
		$ret = array();
		$initquery = "SELECT * FROM posts WHERE uid='$uid' AND parent='0' ORDER BY time DESC LIMIT $limit;";
		if ($pid > 0 and $include == false)
			$initquery = "SELECT * FROM posts WHERE parent='$pid' ORDER BY time DESC LIMIT $commentlim;"; //specify a pid and uid is ignored
		else if ($pid > 0)
			$initquery = "SELECT * FROM posts WHERE parent='$pid' or pid='$pid' ORDER BY time DESC LIMIT $commentlim;";
		$res = sql_query($conn, $initquery);
		while ($row = mysql_fetch_assoc($res))
		{
			$res2 = sql_query($conn, "SELECT * FROM users WHERE uid='" . $row['uid'] . "';");	
			$inarr = array('PID' => $row['pid'],
						   'UID' => $row['uid'],
						   'TIME'=> $row['time'],
						   'TEXT'=> $row['text'],
						   'NAME'=> mysql_fetch_assoc($res2)['name']
						  );
			$inarr['REPLIES'] = null; //deal with that later...fun fact...that used to say $inarr = null;
			array_push($ret, $inarr);
		}
		//now, gather the replies
		for ($i = 0; $i < count($ret); $i = $i + 1)
		{
			$npid = $ret[$i]['PID'];
			$reparr = get_specific_timeline(0, $npid); //ignore user id
			if (count($reparr) > 0)
				$ret[$i]['REPLIES'] = $reparr;
		}
		sql_disconnect($conn);
		return $ret;
		//return sql_return($conn, $ret);
	}

	function get_timeline($uid, $limit = 100) //returns a user's timeline. Follows same format as get_specific_timeline
	{
		$friends = get_friends($uid); //get a list of the user's friends
		$timeline = array();
		$ret = array();
		foreach($friends as $friend)
		{
			$tmp = get_specific_timeline($friend['UID']);
			foreach($tmp as $post)
				array_push($timeline, $post);
		}
		usort($timeline, "timeline_cmp");
		dorecursivesort($timeline);
		if ($limit == 0) return $timeline;
		for ($i = 0; $i < $limit; $i++)
		{
			$ind = count($timeline) - $i - 1;
			if ($ind < 0) continue;
			array_push($ret, $timeline[$ind]);
		}
		return $ret;
	}

?>