<?php
	
	function sql_connect($server, $username, $password)
	{
		$conn = mysql_connect($server, $username, $password, true) or die(mysql_error());
		if (!$conn)
			return NULL;
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

?>