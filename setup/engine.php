<?php

	require_once('sql.php');
	require_once('config.php');

	function setup_error($conn = null) { //yes, I use this style when its >1 line && <5 lines
		if ($conn != null)
			sql_disconnect($conn);
		return false;
	}
	function setup_success($conn = null) {
		if ($conn != null);
			sql_disconnect($conn);
		return true;
	}

	function setup_db($fname)
	{
		$conn = sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS);
		$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$SQL_DB';";
		$bk_lines = "";
		if (sql_query($conn, $query) != null)
			return setup_error($conn); //db exists
		sql_query($conn, "CREATE DATABASE $SQL_DB;");
		sql_select_db($conn, $SQL_DB);
		if (($bk_lines = file($fname)) == null)
			return setup_error($conn);
		foreach($bk_lines as $line)
		{
			if (substr($line, 0, 2) == '--' || $line == '') continue; //skip comments
			$templine .= $line;
			if (substr(trim($line), -1, 1) == ';') //exec when a ; is hit
			{
    			sql_query($conn, $templine) or return setup_error($conn);
    			$templine = '';
			}
		}
		return setup_success($conn);
	}

	function create_admin_user($admin_user, $admin_pass) //returns true on success, false on failure
	{
		$conn = sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS);
		sql_select_db($conn, $SQL_DB);
		$res = sql_query($conn, "SELECT * FROM users WHERE uid='1';");
		if (mysql_fetch_assoc($res) != false)
			return setup_error($conn);
		$_admin_pass = password_hash($admin_pass, PASSWORD_BCRYPT);
		$_admin_user = $admin_user;
		$_admin_name = "Administrator";
		sql_query($conn, "INSERT INTO users(username, name, password) VALUES('$_admin_user', '$_admin_name', '$_admin_pass');");
		$res2 = sql_query($conn, "SELECT * FROM users WHERE username='$_admin_user';");
		$row = mysql_fetch_assoc($res2);
		var_dump($row);
		if (strcmp($row['uid'], "1") != 0)
		{
			$id = $res['uid'];
			sql_query($conn, "DELETE FROM users WHERE uid='$id';");
			return setup_error($conn);
		}
		return setup_success($conn);
	}

?>