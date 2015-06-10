<?php

	include('../engine.php');
	

	$SQL_SERVER = $GLOBALS['SQL_SERVER'];
	$SQL_USER   = $GLOBALS['SQL_USER'];
	$SQL_PASS   = $GLOBALS['SQL_PASS'];
	$SQL_DB     = $GLOBALS['SQL_DB'];

	//if (isset($_GET['action']))
		create_admin_user("admin", "pass");

	function setup_error($conn = null) { //yes, I use this style when its >1 line && <5 lines
		if ($conn != null)
			sql_disconnect($conn);
		echo("setup error");
		return false;
	}
	function setup_success($conn = null) {
		if ($conn != null);
			sql_disconnect($conn);
		echo("setup success<br />");
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
    			sql_query($conn, $templine);
    			$templine = '';
			}
		}
		return setup_success($conn);
	}

	function create_admin_user($admin_user, $admin_pass) //returns true on success, false on failure
	{
		
		$conn = common_connect();
		$res = sql_query($conn, "SELECT * FROM users WHERE uid='1';");
		if (mysql_fetch_assoc($res) != false)
			return setup_error($conn);
		$_admin_pass = password_hash($admin_pass, PASSWORD_BCRYPT);
		$_admin_user = $admin_user;
		$_admin_name = "Administrator";
		sql_query($conn, "INSERT INTO users(username, name, password, permission)" .
				         "VALUES('$_admin_user', '$_admin_name', '$_admin_pass', '65535');");
		$res2 = sql_query($conn, "SELECT * FROM users WHERE username='$_admin_user';");
		$row = mysql_fetch_assoc($res2);
		var_dump($row);
		if (strcmp($row['uid'], "1") != 0)
		{
			$id = $res['uid'];
			sql_query($conn, "DELETE FROM users WHERE uid='$id';");
			return setup_error($conn);
		}
		sql_query($conn, "INSERT INTO friends(owner, friend) VALUES('1', '1');");
		return setup_success($conn);
	}

?>