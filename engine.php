<?php
	require_once('config.php');
	require_once('sql.php');
/*
mysql> describe friends;                                                                                                                                       
+--------+---------+------+-----+---------+-------+                                                                                                            
| Field  | Type    | Null | Key | Default | Extra | 
+--------+---------+------+-----+---------+-------+                                                                                                            
| owner  | int(11) | NO   |     | NULL    |       | //UID of original                                                                                                            
| friend | int(11) | NO   |     | NULL    |       | //person he/she is friends with                                                                                                           
+--------+---------+------+-----+---------+-------+                                                                                                            
mysql> describe posts;                                                                                                                                         
+--------+--------------+------+-----+---------+-------+                                                                                                       
| Field  | Type         | Null | Key | Default | Extra |                                                                                                       
+--------+--------------+------+-----+---------+-------+                                                                                                       
| pid    | int(11)      | NO   | PRI | NULL    |       | //post ID (auto generated)                                                                                                    
| time   | int(11)      | NO   |     | NULL    |       | //time of post (UNIX time)                                                                                                      
| parent | int(11)      | NO   |     | 0       |       | //parent (nonzero if comment, PID of original post/comment)
| uid    | int(11)      | NO   |     | NULL    |       | //uid of poster                                                                                                      
| text   | varchar(255) | NO   |     | NULL    |       | //message contents                                                                                                      
+--------+--------------+------+-----+---------+-------+                                                                                                                                                                                                                                                                                                                                                                                                
mysql> describe users;                                                                                                                                         
+----------+-------------+------+-----+---------+-------+                                                                                                      
| Field    | Type        | Null | Key | Default | Extra |                                                                                                      
+----------+-------------+------+-----+---------+-------+                                                                                                      
| uid      | int(11)     | NO   | PRI | NULL    |       | //user id number (auto generated)                                                                                                     
| name     | int(11)     | NO   |     | NULL    |       | //user's display name
| username | varchar(60) | YES  |     | NULL    |       | //user's login ID                                                                                                     
| password | varchar(72) | YES  |     | NULL    |       | //password_hash($pass, PASSWORD_BCRYPT)
+-------------------------------------------------------+
*/

	function get_friends($uid) //returns an array of the user's friends (as arrays with the following)
		//UID => uid of friend
		//NAME => name of friend
	{
		$conn = sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS);
		sql_select_db($SQL_DB); //now we are in
		$res = sql_query($conn, "select friend from friends where owner='$uid';");
		$ret = array();
		while ($row = mysql_fetch_assoc($res))
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
		sql_disconnect($conn);
		return $ret;
	}

	function get_specific_timeline($uid, $pid = 0, $limit = 50) //returns array of posts in the following format:
		//PID = post id
		//UID = creator
		//TIME = UNIX time of posting
		//TEXT = message content
		//REPLIES = null if no comments, timeline array if comments
	{
		$conn = sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS);
		sql_select_db($SQL_DB);
		$ret = array();
		$initquery = "SELECT * FROM posts WHERE uid='$uid' ORDER BY time LIMIT '$limit';";
		if ($pid > 0)
			$initquery = "SELECT * FROM posts parent='$pid' ORDER BY time LIMIT '$limit';"; //specify a pid and uid is ignored
		$res = sql_query($conn, $initquery);
		while ($row = mysql_fetch_assoc($res))
		{
			$inarr = array('PID' => $row['pid'],
						   'UID' => $row['uid'],
						   'TIME'=> $row['time'],
						   'TEXT'=> $row['text']);
			$inarr = null; //deal with that later
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
	}
	function get_timeline($uid) //returns a user's timeline. Follows same format as get_specific_timeline
	{
		$friends = get_friends($uid); //get a list of the user's friends
	}
?>