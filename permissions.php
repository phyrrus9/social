<?php

	/*permissions:
		0x01  can create posts
		0x02  can create all posts
		0x04  can delete all posts
		0x08  can edit own posts
		0x10  can edit all posts
		0x100 can access admin panel
		0x200 can edit user profiles
		0x400 can edit user access levels
		0x800 can run SQL queries
	 */

	define("ACCESS_POST",              0x0001);
	define("ACCESS_POST_ALL",          0x0002);
	define("ACCESS_DELETE_ALL",        0x0004);
	define("ACCESS_EDIT_OWN",          0x0008);
	define("ACCESS_EDIT_ALL",          0x0010);
	define("ACCESS_ADMIN_PANEL",       0x0100);
	define("ACCESS_ADMIN_ADD_USERS",   0x0200);
	define("ACCESS_ADMIN_EDIT_PROFILE",0x0400);
	define("ACCESS_ADMIN_EDIT_LEVEL",  0x0800);
	define("ACCESS_ADMIN_DELETE_USERS",0x1000);
	define("ACCESS_ADMIN_FLAGS",       0x2000);
	define("ACCESS_ADMIN_SQL",         0x8000);

	function check_perm($perm) { return getuserinfo($_SESSION['userinfo']['uid'])['level'] & $perm; }

	function access_check_me($perm, $opts = null)
		{ return access_check($_SESSION['userinfo']['uid'], $perm, $opts); }

	function access_check($uid, $perm, $opts = null) //unfinished!
	{
		$ret = true;
		$userinfo = getuserinfo($uid);
		if ($perm & ACCESS_POST)
		{
			$pid = $opts['pid'];
			if ($userinfo['level'] & ACCESS_POST_ALL)
				{ $ret &= true; break; }
			else if ($userinfo['level'] & ACCESS_POST)
			{
				if ($pid == 0) { $ret &= true; break; }
				$postinfo = getpostinfo($pid);
				if (isfriendswith($postinfo['UID'], $uid));
			}
			$ret &= false;
		}
		if ($perm & ACCESS_POST_ALL)
			{ $ret &= ($userinfo['level'] & ACCESS_POST_ALL); }
		if ($perm & ACCESS_DELETE_ALL)
			{ $ret &= ($userinfo['level'] & ACCESS_DELETE_ALL); }
		
	}

	function can_post($pid)
	{
		if (check_perm(ACCESS_POST_ALL)) return true;
		if (check_perm(ACCESS_POST))
		{
			if ($pid == 0) return true;
			$postinfo = getpostinfo($pid);
			if (isfriendswith($postinfo['uid']))
				return true;
		}
		return false;
	}

	function can_delete($pid)
	{
		if (check_perm(ACCESS_DELETE_ALL)) return true;
		$postinfo = getpostinfo($pid);
		if ($postinfo['uid'] == $_SESSION['userinfo']['uid']) return true;
		return false;
	}

	function can_edit($pid)
	{
		if (check_perm(ACCESS_EDIT_ALL)) return true;
		$postinfo = getpostinfo($pid);
		return ($postinfo['uid'] == $_SESSION['userinfo']['uid'] && check_perm(ACCESS_EDIT_OWN));
	}

?>