<?php

	require_once('engine.php');
	require_once('ioengine.php');

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

	function dounflag($pid = null)
	{
		if (check_perm(ACCESS_ADMIN_FLAGS))
		{
			if ($pid == null)
				$pid = $_POST['pid'];
			$conn = common_connect();
			sql_query($conn, "UPDATE posts SET flag='0' WHERE pid='$pid';");
			sql_disconnect($conn);
			setresultbox("Post $pid unflagged");
		}
		else
			setresultbox("You are not allowed to manage flags");
	}

	function domanageflags()
	{
		if (!check_perm(ACCESS_ADMIN_FLAGS)) return;
		$flags = getflags();
		if (count($flags) < 1) return;
		$deldis = "";
		if (!check_perm(ACCESS_DELETE_ALL))
			$deldis = "disabled";
		$deledit = "";
		if (!check_perm(ACCESS_EDIT_ALL))
			$deledit = "disabled";
		$ret = "<table border=\"1\">
				<tr>
					<th>pid</th>
					<th>uid</th>
					<th>username</th>
					<th>name</th>
					<th>view</th>
					<th>edit</th>
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
							<form action=\"io.php\" method=\"POST\" \">
								<input type=\"hidden\" name=\"action\" value=\"editpost\" />
								<input type=\"hidden\" name=\"postpid\" value=\"$pid\"/>
								<input type=\"hidden\" name=\"silent\" value=\"true\" />
								<input type=\"hidden\" name=\"unflag\" value=\"true\" />
								<input type=\"submit\" class=\"inlineButton\" value=\"Edit\" $deledit/>
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


?>