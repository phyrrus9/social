<?php
	require_once('config.php');
	require_once('sql.php');
	require_once('sortalgo.php');

	function common_connect() { return sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS, $SQL_DB); }

	function delete_post($uid, $pid)
	{
		$postinfo = getpostinfo($pid);
		if ($postinfo == null) return false; //does not exist
		if ($postinfo['uid'] != $uid and $uid != 1) return false; //not permitted
		$conn = common_connect();
		sql_query($conn, "DELETE FROM posts WHERE parent='$pid' OR pid='$pid';");
		sql_return($conn, true);
	}

	function create_post($uid, $msg, $pid = 0)
	{
		if (strlen($msg) > 256) return false; //message too long
		if (!isfriendswith($uid, getpostinfo($pid)['uid'])) return false; //not permitted
		$conn = common_connect();
		$posttime = time();
		sql_query($conn, "INSERT INTO posts('time', 'parent', 'uid', 'text')," .
				 		 "VALUES('$posttime', '$pid', '$uid', '$msg');");
		sql_return($conn, true);
		
	}

	function getpostinfo($pid)
	{
		$conn = common_connect();
		$res = sql_query("SELECT * FROM posts WHERE pid='$pid';") or
			sql_return($conn, null);
		sql_return($conn, sql_decode($res));
	}

	function isfriendswith($uid1, $uid2) //returns true if friends, false if not
	{
		if ($uid1 == 1) return true; //admin clause
		$conn = common_connect();
		$info = sql_dquery($conn, "SELECT * FROM friends WHERE friend='$uid1';");
		foreach($info as $row)
			if ($row['owner'] == $uid2)
				return sql_return($conn, true);
		return sql_return($conn, false);
	}

	function ismutualfriendswith($uid1, $uid2) { return (isfriendswith($uid1, $uid2) and isfriendswith($uid2, $uid1)) or $uid1 == 1; }

	function get_friends($uid) //returns an array of the user's friends (as arrays with the following)
		//UID => uid of friend
		//NAME => name of friend
	{
		$conn = common_connect();
		$info = sql_dquery($conn, "select friend from friends where owner='$uid';");
		$ret = array();
		foreach($info as $row)
		{
			$ins = array();
			$ins['UID'] = $row['friend']; //get the first
			array_push($ret, $ins);
		}
		for ($i = 0; $i < count($ret); $i = $i + 1)
		{
			$get_uid = $ret[$i]['UID'];
			$res = sql_query($conn, "SELECT name FROM users WHERE uid='$get_uid';");
			$row = mysql_fetch_assoc($res);
			$ret[$i]['NAME'] = $row['name'];
		}
		return sql_return($conn, $ret);
	}

	function get_specific_timeline($uid, $pid = 0, $limit = 50) //returns array of posts in the following format:
		//PID = post id
		//UID = creator
		//TIME = UNIX time of posting
		//TEXT = message content
		//REPLIES = null if no comments, timeline array if comments
	{
		$conn = common_connect();
		$ret = array();
		$initquery = "SELECT * FROM posts WHERE uid='$uid' ORDER BY time LIMIT '$limit';";
		if ($pid > 0)
			$initquery = "SELECT * FROM posts parent='$pid' ORDER BY time LIMIT '$limit';"; //specify a pid and uid is ignored
		$res = sql_query($conn, $initquery);
		while ($row = mysql_fetch_assoc($res))
		{
			$res2 = sql_query($conn, "SELECT * FROM users WHERE uid='" + $row['uid'] + "';");
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
		return sql_return($conn, $ret);
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
		sort_array_asc($timeline, "TIME");
		if ($limit == 0) return $timeline;
		for ($i = 0; $i < $limit; $i++)
			array_push($ret, $timeline[$i]);
		return $ret;
	}

?>