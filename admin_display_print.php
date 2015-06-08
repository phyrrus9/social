<?php

	require_once('engine.php');
	require_once('ioengine.php');

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
	function manageconsole()
	{
		echo("<div class=\"rbox\">");
		if (check_perm(ACCESS_ADMIN_FLAGS)) {
		?>
		<form action="admin.php" method="POST" autocomplete="off">
			<input type="hidden" name="action" value="manageflags" /><br />
			<input type="submit" value="Manage Flags" />
		</form>
		<?php } if (check_perm(ACCESS_ADMIN_EDIT_PROFILE |
							   ACCESS_ADMIN_EDIT_LEVEL   |
							   ACCESS_ADMIN_DELETE_USERS)) { ?>
		<form action="admin.php" method="POST" autocomplete="off">
			<input type="hidden" name="action" value="manageallusers" /><br />
			<input type="submit" value="Select All Users" />
		</form>
		<?php }
		echo("</div>");
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

?>