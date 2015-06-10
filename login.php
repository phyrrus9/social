<link rel="stylesheet" href="style.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('body').css('background-image', 'url(wallhaven-random/wallhaven_random.php)');
    });
</script>

<?php
	require_once('engine.php');

	session_start();

	$operation = "LOGIN";
	if (isset($_POST['action']))
		$operation = $_POST['action'];
	else if (isset($_SESSION['userinfo']))
		$operation = "LOGOUT";

	switch ($operation)
	{
		case "LOGIN":
			if (isset($_POST['logininfo']))
				do_login($_POST['user'], $_POST['pass']);
			else
				showlogin();
			break;
		case "LOGOUT":
			unset($_SESSION['userinfo']);
			session_destroy();
			do_redirect("login.php");
			break;
		case "REGISTER":
			echo("register<br />");
			if (!GET_OPTION('ALLOW_USER_REGISTRATION')) break;
			do_register($_POST['user'], GET_DEFAULT('DISPLAY_NAME'), $_POST['pass']);
			break;
		default:
			die("Who's the smart one here...");
	}
	do_redirect("index.php");

	function do_register($user, $name, $pass)
	{
		$conn = common_connect();
		$res = sql_decode(sql_query($conn, "SELECT * FROM users WHERE username='$user';"));
		if ($res != false)
		{
			sql_disconnect($conn);
			do_redirect("login.php");
		}
		$inspass = password_hash($pass, PASSWORD_BCRYPT);
		$access = GET_DEFAULT('ACCESS');
		$query = "INSERT INTO users(username, name, password, permission) " .
				 "VALUES('$user', '$name', '$inspass', '$access');";
		sql_query($conn, $query);
		$uid = getuidbyusername($user);
		sql_query($conn, "INSERT INTO friends(owner, friend) " .
				 		 "VALUES('$uid','$uid'),('$uid','1')");
		sql_disconnect($conn);
		do_login($user, $pass, $_POST['timezone']);
	}

	function do_login($user, $pass, $timezone = null)
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
			$_SESSION['userinfo']['timezone'] = $timezone == null ?
				$_POST['timezone'] : $timezone;
			return true;
		}
		return false;
	}

	function showlogin()
	{
		?>
		<header>
			<div class="left">
				<form action="login.php" method="POST" autocomplete="off" style="align: right;">
							  <input type="hidden" name="action" value="LOGIN" />
							  <input type="hidden" name="logininfo" value="1" />
							  <input type="hidden" id="zoneinfo" name="timezone" />
					Username: <input type="text" name="user" /> 
					Password: <input type="password" name="pass" /> 
							  <input type="submit" class="inlineButton" value="Log In" />
					<script type="text/javascript" charset="utf-8">
					document.getElementById("zoneinfo").value = jstz.determine().name();
					</script>
				</form>
			</div>
			<div class="right">
				<?php if (GET_OPTION('ALLOW_USER_REGISTRATION')) { ?>
				<form action="login.php" method="POST" autocomplete="off" style="align: right;">
							  <input type="hidden" name="action" value="REGISTER" />
							  <input type="hidden" name="logininfo" value="1" />
							  <input type="hidden" id="zoneinfo" name="timezone" />
					Username: <input type="text" name="user" /> 
					Password: <input type="password" name="pass" />
							  <input type="submit" class="inlineButton" value="Register" />
					<script type="text/javascript" charset="utf-8">
					document.getElementById("zoneinfo").value = jstz.determine().name();
					</script>
				</form>
				<?php } ?>
			</div>
			<br />
		</header>
		<body>
			<div class="genericcontent">
				<center>
					<h1>Welcome to <?php echo $GLOBALS['SETTINGS']['NAME']; ?>!</h1>
					<p>
						I know what you're thinking, how can somebody expect to compete
						with Facebook, Twitter, Reddit, etc? Well, actually it is quite
						simple. The goal of this project was not to compete with them,
						but to provide a better alternative while combining the cool
						parts of each. Keep reading and I will describe what is included
						in this website. Let's go through the features grouped by where
						I got the ideas for them.
					</p>
					<b>Facebook:</b>
					<p>
						Well, the idea for the whole project came from Facebook. Not
						because I like it, but because I dislike it so much. Since
						Facebook became a corporation, there have been so many debates
						about information privacy it isn't even funny. That is what
						inspired me to create this platform. I wanted to build something
						that would never have an issue like that, I made it open source to
						prove that to people. If you ever have a concern about what
						data is even visible to Administrators, simply look at the source
						code (<a href="http://github.com/phyrrus9/social">located here</a>)
					</p>
					<p>
						Anyways, the features I decided to use from Facebook. I really did
						not take much from them. I decided I would use the timeline idea as
						well as the concept of "friends", but that will be described later.
					</p>
					<b>Reddit</b>
					<p>
						I know this will upset some of you, but I decided to take ideas from
						Reddit as well. The one thing I like about reddit was their "comment
						on anything" design and their thread pattern. You will see that that
						is also included in this project.
					</p>
					<b>Twitter</b>
					<p>
						Ah, last but not least is Twitter. I prefer it over other social
						networks, and likewise I took more ideas from it. All timelines are
						public, they can be viewed simply by searching for the username of
						your "friend". That brings up another point, the friend model is also
						taken from Twitter. When you add somebody as a friend, their posts
						instantly show up in your timeline, but you cannot reply to any of
						them unless they also add you as a friend. They will get a request
						when you add them.
					</p>
					<hr width="70%" />
					<p>
						Well, there you have it. I hope this project takes off, because an
						open source social media revolution would be great for the growing
						and ever changing world. What do you say, stand up with me and get
						more open source social networks out there!
					</p>
				</center>
			</div>
		</body>
		<?php
		die();
	}

?>