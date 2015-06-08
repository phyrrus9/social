<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>User Panel</title>
	</head>
<?php
	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();
	changewallpaper();
	if (isset($_POST['action']))
	{
		switch ($_POST['action'])
		{
			case "friend":
				dofriend();
				break;
			case "unfriend":
				dounfriend();
				break;
			case "editprofile":
				doeditprofile();
				break;
			default: break;
		}
	}

	function dofriend()
	{
		$friend = null;
		$uid = $_SESSION['userinfo']['uid'];
		if (!isset($_POST['uid']))
			$friend = getuidbyusername($_POST['username']);
		else
			$friend = $_POST['uid'];
		if ($friend == null) return;
		$friendname = getuserinfo($friend)['name'];
		$conn = common_connect();
		sql_query($conn, "INSERT INTO friends(owner, friend) VALUES('$uid','$friend');");
		sql_disconnect($conn);
		setresultbox("You are now friends with $friendname");
	}

	function dounfriend()
	{
		$uid = $_SESSION['userinfo']['uid'];
		$friend = $_POST['uid'];
		$conn = common_connect();
		sql_query($conn, "DELETE FROM friends WHERE owner='$uid' AND friend='$friend';");
		sql_disconnect($conn);
		setresultbox("You deleted a friend. Sorry they were a bother");
	}

	function doeditprofile()
	{
		$setpass = false;
		$uid = $_SESSION['userinfo']['uid'];
		$name = $_POST['name'];
		$pass = null;
		$info = getuserinfo($uid);
		$conn = common_connect();
		if (isset($_POST['password']))
		{
			$pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
			$setpass = true;
		}
		$query = "UPDATE users SET name='$name' WHERE uid='$uid';";
		if ($setpass == true)
			$query = "UPDATE users SET  name='$name', password='$pass' WHERE uid='$uid';";
		sql_query($conn, $query);
		setresultbox("User information for updated");
	}

	function frienduser()
	{
		?>
		<div class="rbox">
			Add a friend
			<form action="user.php" method="post" autocomplete="off">
				<input type="hidden" name="action" value="friend" />
				<input type="text" name="username" value="username" /><br />
				<input type="submit" class="inlineButton" value="Add Friend" />
			</form>	
			<sub>
				Use this box to add a friend by their username<br />
				If you do not know the username of your friend you should ask
			</sub>
		</div>
		<?php
	}

	function editprofile()
	{
		$info = getuserinfo($_SESSION['userinfo']['uid']);
		$name = $info['name'];
		?>
		<div class="rbox">
			Edit your profile
			<form action="user.php" method="post" autocomplete="off">
				<input type="hidden" name="action" value="editprofile" />
				Username: <input type="text" name="name" value=<?php echo("\"$name\"") ?> /><br />
				Password: <input type="password" name="password" value="" /><br />
				<input type="submit" class="inlineButton" value="Edit Profile" />
			</form>
			<sub>*Leave password field blank to keep old password</sub>
		</div>
		<?php
	}

	function managefriends()
	{
		managemyfriends(); managefriendsofmine();
	}

	function viewtimeline()
	{
		?>
		<div class="rbox">
			<form action="timeline.php" method="POST">
				<input type="hidden" name="uid" value="0" />
				<input type="text" name="name" />
				<input type="submit" class="inlineButton" value="View Timeline" />
			</form>
			<sub>
				If you want to view a user's timeline but they are not<br />
				your friend, you can simply type in their username here<br />
				and click 'View Timeline' to do so.
			</sub>
			<br /><br />
		</div>
		<?php
	}

	function managemyfriends()
	{
		$uid = $_SESSION['userinfo']['uid'];
		$friends = get_friends($uid);
		if (count($friends) <= 2)
			return;
		?>
		<div class="rbox">
			My friends
			<table border="1">
				<tr>
					<th>Name</th>
					<th>Mutual</th>
					<th>Timeline</th>
					<th>Unfriend</th>
				</tr>	
		<?php
		foreach($friends as $friend)
		{
			if ($friend['UID'] == 1 or $friend['UID'] == $uid)
				continue;
			?>
				<tr>
					<td><?php echo $friend['NAME']; ?></td>
					<td>
						<?php
						if (ismutualfriendswith($uid,$friend['UID']))
							echo "Yes";
						else
							echo "No";
						?>
					</td>
					<td>
						<?php echo show_user_timeline($friend['UID']); ?>
					</td>
					<td>
						<form action="user.php" method="POST">
							<input type="hidden" name="action" value="unfriend" />
							<input type="hidden" name="uid" value=<?php echo("\"".$friend['UID']."\""); ?> />
							<input type="submit" class="inlineButton" value="Unfriend" />
						</form>
					</td>
				</tr>
			<?php
		}
		?></table></div><?php
	}

	function managefriendsofmine()
	{
		$uid = $_SESSION['userinfo']['uid'];
		$friends = get_friend_requests($uid);
		if (count($friends) < 1) return;
		?>
		<div class="rbox">
			Friend requests
			<table border="1">
				<tr>
					<th>Name</th>
					<th>Timeline</th>
					<th>Friend</th>
				</tr>	
		<?php
		foreach($friends as $friend)
		{
			if ($friend['UID'] == 1)
				continue;
			?>
				<tr>
					<td><?php echo $friend['NAME']; ?></td>
					<td>
						<form action="timeline.php" method="POST">
							<input type="hidden" name="uid" value=<?php echo("\"".$friend['UID']."\""); ?> />
							<input type="submit" class="inlineButton" value="Timeline" />
						</form>
					</td>
					<td>
						<form action="user.php" method="POST">
							<input type="hidden" name="action" value="friend" />
							<input type="hidden" name="uid" value=<?php echo("\"".$friend['UID']."\""); ?> />
							<input type="submit" class="inlineButton" value="Friend" />
						</form>
					</td>
				</tr>
			<?php
		}
		?></table></div><?php
	}

	function setresultbox($str)
	{ $_SESSION['USER_RESULT_BOX'] = $str; }

	function showresultbox()
	{
		$results = "";
		if (isset($_SESSION['USER_RESULT_BOX']))
			$results = $_SESSION['USER_RESULT_BOX'];
		else return;
		?>
		<div class="rbox">
				<?php echo($results); ?>
		</pre>
		<?php
		unset($_SESSION['USER_RESULT_BOX']);
	}
	
?>
	<header>
		<div class="left">User Panel</div>
		<div class="right">
			<?php echo($_SESSION['userinfo']['name']); ?>
			<?php printtimelinelink(); printadminlink(); printlogoutlink() ?>
		</div><br />
	</header>
	<br />
	<body>
		<div class="user">
			<center>
				<?php frienduser(); editprofile(); viewtimeline(); ?>
			</center><br />
			<center>
				<?php managefriends(); ?>
			</center><br />
			<center>
				<?php showresultbox(); ?>
			</center>
		</div>
	</body>		
</html>