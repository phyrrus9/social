<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>User Panel</title>
	</head>
<?php
	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();

	if (!isset($_POST['uid']))
		do_redirect("index.php");

	function decode_uid()
	{
		$uid = $_POST['uid'];
		if ($uid == 0)
		{
			if (!isset($_POST['name']))
				do_redirect("index.php");
			$uid = getuidbyusername($_POST['name']);
		}
		return $uid;
	}

	function do_timeline($uid)
	{
		$timeline = get_specific_timeline($uid);
		subtimeline($timeline);
	}

	function do_timeline_info($uid)
	{
		$name = getuserinfo($uid)['name'];
		?>
		<center>
			<div class="rbox">
				Showing timeline for <?php echo $name; ?>
			</div>
		</center>
		<?php
	}

?>
	<header>
		<div class="left">Timeline Viewer</div>
		<div class="right">
			<?php echo($_SESSION['userinfo']['name']); ?>
			<?php printtimelinelink(); printuserlink(); printadminlink(); printlogoutlink() ?>
		</div><br />
	</header>
	<br />
	<body>
		<div class="timeline">
			<?php
			$uid = decode_uid();
			do_timeline_info($uid);
			do_timeline($uid); 
			?>
		</div>
	</body>		
</html>