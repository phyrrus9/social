<link rel="stylesheet" href="style.css" />
<?php

	require_once('engine.php');

	function display_timeline($uid)
		/*PID = post id
		  UID = creator
		  TIME = UNIX time of posting
		  TEXT = message content
		  NAME = display name of poster
		  REPLIES = null if no comments, timeline array if comments
		 */
	{
		?>
		<div class="timeline">
			<?php subtimeline(get_timeline($uid), check_perm(ACCESS_ADMIN_FLAGS)); ?>
		</div>
		<?php
	}

	function subtimeline($timeline, $h = false, $s = false)
		{ echo subtimeline_internal($timeline, $h, $s); }

	function subtimeline_internal($timeline, $highlightflagged = false, $silent = false)
		//responsible for formatting the timeline for the user to see
		//format for timeline array:
	{
		$ret = "";
		if (strlen($_SESSION['userinfo']['timezone']) >= 3)
			date_default_timezone_set($_SESSION['userinfo']['timezone']);
		$ret .= "<ul>";
		foreach($timeline as $thread)
		{
			if ($thread == null) continue;
			$highlight = "";
			$tpid = $thread['PID'];
			if ($highlightflagged and ispostflagged($tpid))
				$highlight = "_highlight";
			$ret .= "
				<li class=\"rbox$highlight\">
					<a class=\"hiddenanchor\" name=\"pid$tpid\"
					   id=\"pid$tpid\"></a>
					<div class=\"left\">" . 
						$thread['NAME'] . " | "  . date("D M j Y G:i:s T", $thread['TIME']) . "
					</div>
					<div class=\"right\"> " .
						printflagbutton($tpid) . "&nbsp;" .
						printcommentbutton($tpid) . "&nbsp;" .
						printdeletebutton($tpid) . "&nbsp;" .
						printeditbutton($tpid, $silent) . "
					</div>
					<br />
					<div class=\"message\"> " .
					$thread['TEXT'] . "
					</div>
				</li>";
			if ($thread['REPLIES'] != NULL)
				$ret .= subtimeline_internal($thread['REPLIES'], $highlightflagged, $silent); //gotta love mixed-language recursion
		}
		$ret .= "</ul>";
		return $ret;
	}

	function show_user_timeline($uid, $txt = "Timeline")
	{
		return 
		"<form action=\"timeline.php\" method=\"POST\" style=\"display: inline\">
			<input type=\"hidden\" name=\"uid\" value=\"$uid\" />
			<input type=\"submit\" class=\"inlineButton\" value=\"$txt\" />
		</form>";
	}

	function printcommentform($pid)
	{
		?><center>
		<div class="alone_rbox">
			<?php if ($pid == 0) { ?>Create post
			<?php } else { ?>Post a comment <?php } ?><br /><br />
		<form action="io.php" method="POST">
			<input type="hidden" name="action" value="postcomment" />
			<input type="hidden" name="commentpid" value=<?php echo("\"$pid\""); ?> />
			<textarea rows="4" cols="50" name="commenttext"></textarea>
			<input type="submit" />
		</form>
		</div>
		<?php if ($pid > 0) { ?>
		<div class="timeline">
			<?php
			$timeline = get_specific_timeline(0, $pid, 40, true);
			subtimeline($timeline);
			?>	
		</div> <?php } ?>
		</center><?php
	}

	function printflagbutton($pid)
	{
		return "
		<form id=\"comment$pid\" action=\"io.php\" method=\"POST\" style=\"display: inline;\">
			<input type=\"hidden\" name=\"action\" value=\"flagpost\" />
			<input type=\"hidden\" name=\"postpid\" value=\"$pid\"/>
			<input type=\"submit\" class=\"inlineButton\" value=\"Flag\" />
		</form>
		";
	}

	function printcommentbutton($pid)
	{
		if (!can_post($pid)) return "";
		return "
		<form id=\"comment$pid\" action=\"io.php\" method=\"POST\" style=\"display: inline;\">
			<input type=\"hidden\" name=\"action\" value=\"createcomment\" />
			<input type=\"hidden\" name=\"commentpid\" value=\"$pid\"/>
			<input type=\"submit\" class=\"inlineButton\" value=\"Comment\" />
		</form>
		";
	}

	function printdeletebutton($pid)
	{
		if (!can_delete($pid)) return "";
		return "
		<form id=\"delete$pid\" action=\"io.php\" method=\"POST\" style=\"display: inline;\">
			<input type=\"hidden\" name=\"action\" value=\"deletepost\" />
			<input type=\"hidden\" name=\"postpid\" value=\"$pid\"/>
			<input type=\"submit\" class=\"inlineButton\" value=\"Delete\" />
		</form>
		";
	}

	function printeditbutton($pid, $silent = false)
	{
		$silentstr = "false";
		if ($silent)
			$silentstr = "true";
		if (!can_edit($pid)) return "";
		return "
		<form id=\"edit$pid\" action=\"io.php\" method=\"POST\" style=\"display: inline;\">
			<input type=\"hidden\" name=\"action\" value=\"editpost\" />
			<input type=\"hidden\" name=\"postpid\" value=\"$pid\"/>
			<input type=\"hidden\" name=\"silent\" value=\"$silentstr\" />
			<input type=\"hidden\" name=\"unflag\" value=\"false\" />
			<input type=\"submit\" class=\"inlineButton\" value=\"Edit\" />
		</form>
		";
	}

	function printpostbutton()
	{
		if (!can_post(0)) return;
		?>
		<form action="io.php" method="POST" style="display: inline;">
			<input type="hidden" name="action" value="createcomment" />
			<input type="hidden" name="commentpid" value="0" />
			<input type="submit" class="newPostButton" value="New Post" style="display: inline;" />
		</form>
		<?php
	}

	function printeditform($pid)
	{
		$silent = $_POST['silent'];
		$unflag = $_POST['unflag'];
		?>
		<div class="alone_rbox">
		<form action="io.php" method="POST">
			<input type="hidden" name="action" value="publishedit" />
			<input type="hidden" name="postpid" value=<?php echo("\"$pid\""); ?> />
			<input type="hidden" name="silent" value=<?php echo("\"$silent\"") ?> />
			<input type="hidden" name="unflag" value=<?php echo("\"$unflag\"") ?> />
			<textarea rows="4" cols="50" name="postmsg"><?php echo(br2nl(getpostinfo($pid)['text'])); ?></textarea>
			<input type="submit" />
		</form>
		</div>
		<?php
	}

	function printadminlink()
	{
		if (!check_perm(ACCESS_ADMIN_PANEL)) return;
		?>|&nbsp;&nbsp;<a href="admin.php">Admin Panel</a>&nbsp;&nbsp;<?php
	}
	
	function printuserlink()
	{
		if (!isset($_SESSION['userinfo'])) return;
		?>|&nbsp;&nbsp;<a href="user.php">User Panel</a>&nbsp;&nbsp;<?php
	}

	function printtimelinelink()
	{
		if (!isset($_SESSION['userinfo'])) return;
		?>|&nbsp;&nbsp;<a href="index.php">Timeline</a>&nbsp;&nbsp;<?php
	}

	function printlogoutlink()
	{
		if (!isset($_SESSION['userinfo'])) return;
		?>|&nbsp;&nbsp;<a href="login.php">Log Out</a>&nbsp;&nbsp;<?php
	}

?>