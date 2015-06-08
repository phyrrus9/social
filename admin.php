<link rel="stylesheet" href="style.css" />
<?php

	require_once('engine.php');
	require_once('ioengine.php');
	require_once('admin_do.php');
	require_once('admin_display_print.php');
	checklogin();
	changewallpaper();
	if (!check_perm(ACCESS_ADMIN_PANEL)) do_redirect("index.php#nopermission");

	if (isset($_POST['action']))
	{
		switch ($_POST['action'])
		{
			case "searchusers": dosearchusers(); break;
			case "sqlquery": dosqlquery(); break;
			case "adduser": doadduser(); break;
			case "deleteuser": dodeleteuser(); break;
			case "editlevel": displayleveledit(); break;
			case "updatelevel": doupdatelevel(); break;
			case "updateprofile": doupdateprofile(); break;
			case "editprofile": displayprofileedit(); break;
			case "manageflags": domanageflags(); break;
			case "viewpost": doviewpost(); break;
			case "deletepost": dodeletepost(); domanageflags(); break;
			case "unflagpost": dounflag(); domanageflags(); break;
			case "manageallusers": dosearchusers_query("SELECT * FROM users;"); break;
			default: break;
		}
	}

	function setresultbox($str)
		{ $_SESSION['ADMIN_RESULT_BOX'] = $str; }

	function getflags()
	{
		$ret = array();
		$conn = common_connect();
		$res = sql_dquery($conn, "SELECT * FROM posts WHERE flag='1';");
		foreach($res as $row)
			array_push($ret, $row);
		sql_disconnect($conn);
		return $ret;
	}

	function getpermissionstates_internal($perm)
	{
		$access = array(
					'post'         => ' ',
					'post_all'     => ' ',
					'delete_all'   => ' ',
					'edit_own'     => ' ',
					'edit_all'     => ' ',
					'admin_panel'  => ' ',
					'add_users'    => ' ',
					'edit_profile' => ' ',
					'edit_level'   => ' ',
					'delete_users' => ' ',
					'flags'		   => ' ',
					'sql'          => ' '
				);
		if ($perm & ACCESS_POST) $access['post'] = 'checked';
		if ($perm & ACCESS_POST_ALL) $access['post_all'] = 'checked';
		if ($perm & ACCESS_DELETE_ALL) $access['delete_all'] = 'checked';
		if ($perm & ACCESS_EDIT_OWN) $access['edit_own'] = 'checked';
		if ($perm & ACCESS_EDIT_ALL) $access['edit_all'] = 'checked';
		if ($perm & ACCESS_ADMIN_PANEL) $access['admin_panel'] = 'checked';
		if ($perm & ACCESS_ADMIN_ADD_USERS) $access['add_users'] = 'checked';
		if ($perm & ACCESS_ADMIN_EDIT_PROFILE) $access['edit_profile'] = 'checked';
		if ($perm & ACCESS_ADMIN_EDIT_LEVEL) $access['edit_level'] = 'checked';
		if ($perm & ACCESS_ADMIN_DELETE_USERS) $access['delete_users'] = 'checked';
		if ($perm & ACCESS_ADMIN_FLAGS) $access['flags'] = 'checked';
		if ($perm & ACCESS_ADMIN_SQL) $access['sql'] = 'checked';
		return $access;
	}

	function getpermissionstates($perm, $disabled = "disabled", $access_enabled = null)
	{
		$ret = "";
		$access = getpermissionstates_internal($perm);
		$i = 0;
		foreach($access as $key => $value)
		{
			$tmp_disabled = $disabled;
			if ($access_enabled != null)
				if (isset($access_enabled[$key]))
					if (strcmp($access_enabled[$key], "checked") == 0)
						$tmp_disabled = "";
			if (!strcmp($key, "edit_all") || !strcmp($key, "edit_level"))
				$ret .= "<br />";
			$ret .= "[$key<input type=\"checkbox\" name=\"$key\" $value $tmp_disabled />]";
			$i++;
		}
		return $ret;
	}

	function resultbox()
	{
		$results = "";
		if (isset($_SESSION['ADMIN_RESULT_BOX']))
			$results = $_SESSION['ADMIN_RESULT_BOX'];
		else return;
		?>
		<div class="rbox">
				<?php echo($results); ?>
		</pre>
		<?php
		unset($_SESSION['ADMIN_RESULT_BOX']);
	}

?>

<header>
<div class="left">Admin Panel</div>
<div class="right">
	<a href="index.php">Timeline</a>
	<?php printlogoutlink(); ?>
</div><br />
</header>
<div class="admin">
	<center><?php adduser(); searchusers(); sqlquery(); manageconsole(); ?></center><br />
	<br />
	<br />
	<center><?php resultbox(); ?></center>
</div>