<?php
	
	function sql_connect($server, $username, $password, $db = null)
	{
		$conn = mysql_connect($server, $username, $password, true) or die(mysql_error());
		if ($db != null) sql_select_db($conn, $db);
		return $conn;
	}
	function sql_disconnect($conn)
	{
		mysql_close($conn);
	}
	function sql_select_db($conn, $db)
	{
		$query = "USE " . $db;
		$res = mysql_query($query, $conn);
	}
	function sql_query($conn, $query)
	{
		return mysql_query($query, $conn);
	}
	function sql_decode_many($res)
	{
		$ret = array();
		while (($row = mysql_fetch_assoc($res)))
			array_push($ret, $row);
		return $ret;
	}
	function sql_decode($res)
	{
		return mysql_fetch_assoc($res); //just for API readability
	}
	function sql_dquery($conn, $query) //query and decode_many :)
	{
		$ret = array();
		$res = mysql_query($query, $conn) or die(mysql_error());
		while (($row = mysql_fetch_assoc($res)))
			array_push($ret, $row);
		return $ret;
	}
	function sql_return($conn, $val)
	{
		sql_disconnect($conn);
		return val;
	}

?>