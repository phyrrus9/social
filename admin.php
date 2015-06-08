<link rel="stylesheet" href="style.css" />
<?php

	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();
	if (!check_perm(ACCESS_ADMIN_PANEL)) do_redirect("index.php#nopermission");

	if (isset($_POST['action']))
	{
		switch ($_POST['action'])
		{
			case "searchusers":
				dosearchusers();
				break;
			case "sqlquery":
				dosqlquery();
				break;
			case "adduser":
				doadduser();
				break;
			case "deleteuser":
				dodeleteuser();
				break;
			case "editlevel":
				displayleveledit();
				break;
			case "updatelevel":
				doupdatelevel();
				break;
			case "updateprofile":
				doupdateprofile();
				break;
			case "editprofile":
				displayprofileedit();
				break;
			case "manageflags":
				domanageflags();
				break;
			case "viewpost":
				doviewpost();
				break;
			case "deletepost":
				dodeletepost();
				domanageflags();
				break;
			case "unflagpost":
				dounflag();
				domanageflags();
				break;
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

	function doviewpost()
	{
		$pid = $_POST['pid'];
		$timeline = get_specific_timeline(0, $pid, 50, true);
		$res = "<div class=\"timeline\">" .
				subtimeline_internal($timeline, true, true) .
			    "</div>";
		setresultbox($res);
	}

	function dodeletepost()
	{
		$pid = $_POST['pid'];
		$uid = $_SESSION['userinfo']['uid']; //fixed UID bug from v0.1
		if (!check_perm(ACCESS_ADMIN_FLAGS) && can_delete($pid))
		{
			setresultbox("You are not allowed to delete posts");
			return;
		}
		delete_post($uid, $pid);
		setresultbox("Post $pid deleted");
	}

	function dounflag()
	{
		if (check_perm(ACCESS_ADMIN_FLAGS))
		{
			$pid = $_POST['pid'];
			$conn = common_connect();
			sql_query($conn, "UPDATE posts SET flag='0' WHERE pid='$pid';");
			sql_disconnect($conn);
			setresultbox("Post $pid unflagged");
		}
		else
			setresultbox("You are not allowed to manage flags");
	}

	function manageflags()
	{
		if (!check_perm(ACCESS_ADMIN_FLAGS)) return;
		?>
		<div class="rbox">
			<form action="admin.php" method="POST" autocomplete="off">
				<input type="hidden" name="action" value="manageflags" /><br />
				<input type="submit" value="Manage Flags" />
			</form>
		</div>
		<?php
	}


	function domanageflags()
	{
		if (!check_perm(ACCESS_ADMIN_FLAGS)) return;
		$flags = getflags();
		if (count($flags) < 1) return;
		$deldis = "";
		if (!check_perm(ACCESS_DELETE_ALL))
			$deldis = "disabled";
		$ret = "<table border=\"1\">
				<tr>
					<th>pid</th>
					<th>uid</th>
					<th>username</th>
					<th>name</th>
					<th>view</th>
					<th>delete</th>
					<th>unflag</th>
				</tr>";
		foreach($flags as $flag)
		{
			$pid = $flag['pid'];
			$uid = $flag['uid'];
			$info = getuserinfo($uid);
			$uname = $info['user'];
			$name = $info['name'];
			$ret .= "<tr>
						<td>$pid</td>
						<td>$uid</td>
						<td>$uname</td>
						<td>$name</td>
						<td>
							<form action=\"admin.php\" method=\"POST\">
								<input type=\"hidden\" name=\"action\" value=\"viewpost\" />
								<input type=\"hidden\" name=\"pid\" value=\"$pid\" />
								<input type=\"submit\" class=\"inlineButton\" value=\"View\" />
							</form>
						</td>
						<td>
							<form action=\"admin.php\" method=\"POST\">
								<input type=\"hidden\" name=\"action\" value=\"deletepost\" />
								<input type=\"hidden\" name=\"pid\" value=\"$pid\" />
								<input type=\"submit\" class=\"inlineButton\" value=\"Delete\" $deldis />
							</form>
						</td>
						<td>
							<form action=\"admin.php\" method=\"POST\">
								<input type=\"hidden\" name=\"action\" value=\"unflagpost\" />
								<input type=\"hidden\" name=\"pid\" value=\"$pid\" />
								<input type=\"submit\" class=\"inlineButton\" value=\"Unflag\" />
							</form>
						</td>
					</tr>";
		}
		$ret .= "</table>";
		setresultbox($ret);
	}

	function adduser()
	{
		if (!check_perm(ACCESS_ADMIN_ADD_USERS)) return;
		?>
		<div class="rbox">
			Create User Account<br />
			<form action="admin.php" method="POST" autocomplete="off">
				<input type="hidden" name="action" value="adduser" /><br />
				<input type="text" name="username" value="username" /><br />
				<input type="text" name="name" value="display name" /><br />
				<input type="password" name="password" value="password" />
				<input type="submit" value="Submit" />
			</form>
		</div>
		<?php
	}

	function doadduser()
	{
		if (!check_perm(ACCESS_ADMIN_ADD_USERS)) return;
		$uname = $_POST['username'];
		$name = $_POST['name'];
		$pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
		$conn = common_connect();
		$res = sql_dquery($conn, "SELECT * FROM users WHERE username='$uname'");
		if ($res == null)
		{
			sql_query($conn, "INSERT INTO users(username,password,name) VALUES('$uname','$pass','$name');");
			$res = sql_dquery($conn, "SELECT * FROM users WHERE username='$uname'");
			if ($res == null)
				setresultbox("User $uname was not created!");
			else
			{
				$userid = $res[0]['uid'];
				$query = "INSERT INTO friends(owner,friend) VALUES('$userid','1'),('$userid','$userid');";
				sql_query($conn, $query);
				setresultbox("User $uname($userid) created!");
			}
		}
		else
			setresultbox("User $uname already exists!");
		sql_disconnect($conn);
	}

	function printadmineditprofile($uid)
	{
		$disabled = "";
		if (!check_perm(ACCESS_ADMIN_EDIT_PROFILE)) $disabled = "disabled";
		return "<form action=\"admin.php\" method=\"POST\">" .
			   "<input type=\"hidden\" name=\"action\" value=\"editprofile\" />" .
			   "<input type=\"hidden\" name=\"edituid\" value=\"$uid\" />" .
			   "<input class=\"inlineButton\" type=\"submit\" value=\"Edit Profile\" $disabled />" .
			   "</form>";
	}
	function printadmineditlevel($uid)
	{
		$disabled = "";
		if (!check_perm(ACCESS_ADMIN_EDIT_LEVEL)) $disabled = "disabled";
		return "<form action=\"admin.php\" method=\"POST\">" .
			   "<input type=\"hidden\" name=\"action\" value=\"editlevel\" />" .
			   "<input type=\"hidden\" name=\"edituid\" value=\"$uid\" />" .
			   "<input class=\"inlineButton\" type=\"submit\" value=\"Edit Level\" $disabled />" .
			   "</form>";
	}
	function printadmindelete($uid)
	{
		$disabled = "";
		if (!check_perm(ACCESS_ADMIN_DELETE_USERS)) $disabled = "disabled";
		return "<form action=\"admin.php\" method=\"POST\">" .
			   "<input type=\"hidden\" name=\"action\" value=\"deleteuser\" />" .
			   "<input type=\"hidden\" name=\"deleteuid\" value=\"$uid\" />" .
			   "<input class=\"inlineButton\" type=\"submit\" value=\"Delete User\" $disabled />" .
			   "</form>";
	}

	function doupdateprofile()
	{
		if (check_perm(ACCESS_ADMIN_EDIT_PROFILE))
		{
			$setpass = false;
			$uid = $_POST['edituid'];
			$uname = $_POST['username'];
			$name = $_POST['name'];
			$pass = null;
			$info = getuserinfo($uid);
			$conn = common_connect();
			if (isset($_POST['pass']))
			{
				$pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
				$setpass = true;
			}
			$query = "UPDATE users SET username='$uname', name='$name' WHERE uid='$uid';";
			if ($setpass == true)
				$query = "UPDATE users SET username='$uname', name='$name', password='$pass' WHERE uid='$uid';";
			if (strcmp($uname, $info['user']) != 0) //then we have to check it
			{
				$res = sql_dquery($conn, "SELECT * FROM users WHERE username='$uname';");
				if ($res != null)
				{
					sql_disconnect($conn);
					setresultbox("Username $uname was already in use");
					return;
				}
			}
			sql_query($conn, $query);
			setresultbox("User information for user $uname($uid) updated");
		}
		else
			setresultbox("You are not allowed to edit user profiles");
	}

	function displayprofileedit()
	{
		if (check_perm(ACCESS_ADMIN_EDIT_PROFILE))
		{
			$uid = $_POST['edituid'];
			$userinfo = getuserinfo($uid);
			$ret =  "Edit profile of UID=$uid<br />" .
					"<form action=\"admin.php\" method=\"POST\">" .
					"[Username <input type=\"text\" name=\"username\" value=\"" . $userinfo['user'] . "\" />]<br />" .
					"[Display &nbsp;<input type=\"text\" name=\"name\" value=\"" . $userinfo['name'] . "\" />]<br />" .
					"[Password <input type=\"text\" name=\"pass\" value=\"\" />]<br />" .
					"<input type=\"hidden\" name=\"action\" value=\"updateprofile\" />" .
					"<input type=\"hidden\" name=\"edituid\" value=\"$uid\" />" .
					"<input type=\"submit\" value=\"Update Profile\" />" .
					"</form><br /><br />" .
					"<sub>*Leave password blank if you do not wish to modify it</sub><br />";
			setresultbox($ret);
		}
		else
			setresultbox("You are not allowed to edit user profiles");
	}

	function displayleveledit()
	{
		if (check_perm(ACCESS_ADMIN_EDIT_LEVEL))
		{
			$ret = "";
			$uid = $_POST['edituid'];
			$me = $_SESSION['userinfo']['uid'];
			$ret .= "Edit user permissions for UID=$uid<br />";
			$conn = common_connect();
			$res = sql_dquery($conn, "SELECT * FROM users WHERE uid='$uid';")[0]['permission'];
			$res2= sql_dquery($conn, "SELECT * FROM users WHERE uid='$me';")[0]['permission'];
			sql_disconnect($conn);
			$ret .= "<form action=\"admin.php\" method=\"POST\">" .
			getpermissionstates($res, "disabled", getpermissionstates_internal($res2)) . "<br />" .
			"<input type=\"hidden\" name=\"action\" value=\"updatelevel\" />" .
			"<input type=\"hidden\" name=\"edituid\" value=\"$uid\" />" .
			"<input type=\"submit\" value=\"Update Level\" />" .
			"</form>";
			setresultbox($ret);
		}
		else
			setresultbox("You are not permitted to edit user levels");
	}

	function doupdatelevel()
	{
		//variable defined and value='on' when checked
		$uid = $_POST['edituid'];
		$access = 0;
		foreach($_POST as $key => $value)
		{
			switch ($key)
			{
				case "post": $access |= ACCESS_POST; break;
				case "post_all": $access |= ACCESS_POST_ALL; break;
				case "delete_all": $access |= ACCESS_DELETE_ALL; break;
				case "edit_own": $access |= ACCESS_EDIT_OWN; break;
				case "edit_all": $access |= ACCESS_EDIT_ALL; break;
				case "admin_panel": $access |= ACCESS_ADMIN_PANEL; break;
				case "add_users": $access |= ACCESS_ADMIN_ADD_USERS; break;
				case "edit_profile": $access |= ACCESS_ADMIN_EDIT_PROFILE; break;
				case "edit_level": $access |= ACCESS_ADMIN_EDIT_LEVEL; break;
				case "delete_users": $access |= ACCESS_ADMIN_DELETE_USERS; break;
				case "flags": $access |= ACCESS_ADMIN_FLAGS; break;
				case "sql": $access |= ACCESS_ADMIN_SQL; break;
				default: break;
			}
		}
		$conn = common_connect();
		sql_query($conn, "UPDATE users SET permission='$access' WHERE uid='$uid';");
		sql_disconnect($conn);
		setresultbox("Level updated for uid $uid<br />" . 
					 getpermissionstates($access) . "<br />" . printadmineditlevel($uid));
	}

	function dodeleteuser()
	{
		if (check_perm(ACCESS_ADMIN_DELETE_USERS))
		{
			$uid = $_POST['deleteuid'];
			if ($uid != 1)
			{
				$conn = common_connect();
				sql_query($conn, "DELETE FROM users WHERE uid='$uid';");
				sql_disconnect($conn);
				setresultbox("User #$uid deleted");
			}
			else
				setresultbox("You cannot delete the super admin");
		}
		else
			setresultbox("You are not permitted to delete users");
	}

	function searchusers()
	{
		if (!check_perm(ACCESS_ADMIN_EDIT_PROFILE |
					    ACCESS_ADMIN_EDIT_LEVEL   |
					    ACCESS_ADMIN_DELETE_USERS)) return;
		?>
		<div class="rbox">
			Search Users<br />
			<form action="admin.php" method="POST" autocomplete="off">
				<input type="hidden" name="action" value="searchusers" /><br />
				<input type="text" name="searchparam" value="Value" /><br />
				<select name="searchby">
					<option value="uid">UID</option>
					<option value="username">Username</option>
					<option value="name">Display Name</option>
					<option value="sql">Custom (SQL Syntax)</option>
				</select>
				<input type="submit" value="Submit" />
			</form>
		</div>
		<?php
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
		if ($perm & ACCESS_POST)
			$access['post'] = 'checked';
		if ($perm & ACCESS_POST_ALL)
			$access['post_all'] = 'checked';
		if ($perm & ACCESS_DELETE_ALL)
			$access['delete_all'] = 'checked';
		if ($perm & ACCESS_EDIT_OWN)
			$access['edit_own'] = 'checked';
		if ($perm & ACCESS_EDIT_ALL)
			$access['edit_all'] = 'checked';
		if ($perm & ACCESS_ADMIN_PANEL)
			$access['admin_panel'] = 'checked';
		if ($perm & ACCESS_ADMIN_ADD_USERS)
			$access['add_users'] = 'checked';
		if ($perm & ACCESS_ADMIN_EDIT_PROFILE)
			$access['edit_profile'] = 'checked';
		if ($perm & ACCESS_ADMIN_EDIT_LEVEL)
			$access['edit_level'] = 'checked';
		if ($perm & ACCESS_ADMIN_DELETE_USERS)
			$access['delete_users'] = 'checked';
		if ($perm & ACCESS_ADMIN_FLAGS)
			$access['flags'] = 'checked';
		if ($perm & ACCESS_ADMIN_SQL)
			$access['sql'] = 'checked';
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

	function dosearchusers()
	{
		$query = "SELECT * FROM users ";
		$searchparam = $_POST['searchparam'];
		$searchby = $_POST['searchby'];
		$ret = "Search value: $searchparam by $searchby<br />";
		switch ($searchby)
		{
			case "uid":
				$query .= "WHERE uid='$searchparam';";
				break;
			case "username":
				$query .= "WHERE username='$searchparam';";
				break;
			case "name":
				$query .= "WHERE name='$searchparam';";
				break;
			case "sql":
				$query .= $searchparam . ";";
				break;
			default:
				setresultbox("Unimplemented search field: " . $searchby);
				do_redirect("admin.php");
				return; //safeguard
				break; //just for funsies, code never gets here (hopefully)
		}
		$conn = common_connect();
		$res = sql_dquery($conn, $query);
		sql_disconnect($conn);
		$ret .= "<table border=\"1\"><tr><th>UID</th><th>Username</th><th>Name</th>" .
				"<th>Level</th><th>Edit:Profile</th><th>Edit:Level</th>" .
				"<th>Edit:Delete</th></tr>";
		foreach($res as $row)
		{
			$info_uid = $row['uid'];
			$info_username = $row['username'];
			$info_name = $row['name'];
			$info_level = $row['permission'];
			$level_str = getpermissionstates($info_level);
			$ret .=
				"<tr>" .
				"<td>$info_uid</td>" .
				"<td>$info_username</td>" .
				"<td>$info_name</td>" .
				"<td>$level_str</td>" .
				"<td>" . printadmineditprofile($info_uid) . "</td>" .
				"<td>" . printadmineditlevel($info_uid) . "</td>" .
				"<td>" . printadmindelete($info_uid) . "</td>" .
				"</tr>";
		}
		$ret .= "</table>";
		setresultbox($ret);
	}

	function sqlquery()
	{
		if (!check_perm(ACCESS_ADMIN_SQL)) return;
		?>
		<div class="rbox">
			SQL Query<br />
			<form action="admin.php" method="POST" autocomplete="off">
				<input type="hidden" name="action" value="sqlquery" /><br />
				<textarea rows="4" cols="50" name="sqlqueryvalue">-- QUERY</textarea>
				<input type="submit" value="Submit" />
			</form>
		</div>
		<?php
	}

	function dosqlquery($recursed = 0, $initial = "")
	{
		if (!check_perm(ACCESS_ADMIN_SQL))
		{
			setresultbox("Not permitted to run SQL queries");
			return;
		}
		$remains = null;
		$query = $_POST['sqlqueryvalue'];
		if ($recursed == 1)
		{
			if (strlen($initial) < 5)
				return ""; //break out
			else
				$query = $initial;
		}
		$query_arr = explode(";", $query);
		$query = $query_arr[0];
		if (count($query_arr) > 1)
		{
			$remains = "";
			for ($i = 1; $i < count($query_arr); $i++)
				$remains .= $query_arr[$i] . ';';
			while (strstr($remains, ";;") != false)
				$remains = str_replace(";;", ";", $remains); //stupid empty query bug
		}
		$ret = "Query: $query<br />";
		$conn = common_connect();
		$res = sql_dquery($conn, $query);
		sql_disconnect($conn);
		$ret .= "<table border=\"1\"><tr>";
		if ($res == null)
		{
			$ret .= "<td>No data returned for query</td></tr>";
		}
		else
		{
			$keys = array_keys($res[0]);
			foreach($keys as $key)
				$ret .= "<th>$key</th>";
			$ret .= "</tr>";
			foreach($res as $arr)
			{
				$ret .= "<tr>";
				$values = array_values($arr);
				foreach($values as $value)
					$ret .= "<td>".htmlspecialchars($value)."</td>";
				$ret .= "</tr>";
			}
		}
		$ret .= "</table>" . "<br />" . dosqlquery(1, $remains);
		if ($recursed == 0)
			setresultbox($ret);
		else
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
	<center><?php adduser(); searchusers(); sqlquery(); manageflags(); ?></center><br />
	<br />
	<br />
	<center><?php resultbox(); ?></center>
</div>