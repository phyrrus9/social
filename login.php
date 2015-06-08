<link rel="stylesheet" href="style.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js"></script>

<?php
	require_once('engine.php');

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
			do_redirect("login.php");
		break;
		default:
			die("Who's the smart one here...");
	}
	do_redirect("index.php");

	function do_login($user, $pass)
	{
		$conn = common_connect();
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
			$_SESSION['userinfo'] = getuserinfo($row['uid']);
			$_SESSION['userinfo']['timezone'] = $_POST['timezone'];
			return true;
		}
		return false;
	}

	function showlogin()
	{
		?>
		<header>
			<form action="login.php" method="POST" autocomplete="off">
						  <input type="hidden" name="logininfo" value="1" />
						  <input type="hidden" id="zoneinfo" name="timezone" />
				Username: <input type="text" name="loginuser" /> 
				Password: <input type="password" name="loginpass" /> 
						  <input type="submit" value="Submit" />
				<script type="text/javascript" charset="utf-8">
				document.getElementById("zoneinfo").value = jstz.determine().name();
				</script>
			</form>
		</header>
		<?php
		die();
	}

?>