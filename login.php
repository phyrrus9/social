<?php
	require_once('sql.php');
	require_once('config.php');

	session_start();

	$operation = "LOGIN";
	if (isset($_SESSION['userinfo']))
		$operation = "LOGOUT";

	switch ($operation)
	{
		case "LOGIN":
			if (isset($_POST['logininfo']))
				do_login($_POST['loginuser'], $_POST['loginpass']);
			else
				showlogin();
		break;
		case "LOGOUT":
			unset($_SESSION['userinfo']);
			session_destroy();
		break;
		default:
			die("Who's the smart one here...");
	}
	echo("<script>window.location.assign(\"index.php\");</script>");

	function do_login($user, $pass)
	{
		$conn = sql_connect($SQL_SERVER, $SQL_USER, $SQL_PASS);
		sql_select_db($conn, $SQL_DB);
		$res = sql_query($conn, "SELECT * FROM users WHERE username='$user';");
		$row = null; //just for keepsake
		if (!($row = mysql_fetch_assoc($res)))
		{
			sql_disconnect($conn);
			return false;
		}
		sql_disconnect($conn);
		if (password_verify($pass, $row['password']))
		{
			$_SESSION['userinfo'] = array(
				'uid'  => $row['uid'],
				'user' => $row['username'],
				'name' => $row['name']);
			return true;
		}
		return false;
	}

	function showlogin()
	{
		?>
		<form action="login.php" method="POST">
			Username: <input type="text" name="loginuser" /> 
			Password: <input type="text" name="loginpass" /> 
					  <input type="submit" />
		</form>
		<?php
	}

?>