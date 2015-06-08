<?php

	require_once('engine.php');
	require_once('ioengine.php');

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
				sql_query($conn, "DELETE FROM friends WHERE owner='$uid' OR friend='$uid';");
				sql_disconnect($conn);
				setresultbox("User #$uid deleted");
			}
			else
				setresultbox("You cannot delete the super admin");
		}
		else
			setresultbox("You are not permitted to delete users");
	}

	function dosearchusers()
	{
		$query = "SELECT * FROM users ";
		$searchparam = $_POST['searchparam'];
		$searchby = $_POST['searchby'];
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
		dosearchusers_query($query, $searchby, $searchparam);
	}
		
	function dosearchusers_query($query, $searchby = "auto", $searchparam = "auto")
	{
		$ret = "Search value: $searchparam by $searchby<br />";
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

?>