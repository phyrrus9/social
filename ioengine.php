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
			<?php subtimeline(get_timeline($uid)); ?>
		</div>
		<?php
	}

	function subtimeline($timeline)
		//responsible for formatting the timeline for the user to see
		//format for timeline array:
	{
		echo("<ul>");
		foreach($timeline as $thread)
		{
			if ($thread == null) continue;
			$tpid = $thread['PID'];
			?>
				<li class="rbox">
					<a class="hiddenanchor" name=<?php echo("\"pid" . $tpid . "\""); ?>
					   id=<?php echo("\"pid" . $tpid . "\""); ?>></a>
					<div class="left">
						<?php echo($thread['NAME'] . " | " . $thread['TIME']); ?>
					</div>
					<div class="right">
						<?php printcommentbutton($tpid); ?> &nbsp;
						<?php printdeletebutton($tpid); ?> &nbsp;
						<?php printeditbutton($tpid); ?>
					</div>
					<br />
					<?php echo($thread['TEXT']); ?>
				</li>	
			<?php
			if ($thread['REPLIES'] != NULL)
				subtimeline($thread['REPLIES']); //gotta love mixed-language recursion
		}
		echo("</ul>");
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
			$timeline = get_specific_timeline(0, $pid);
			subtimeline($timeline);
			?>	
		</div> <?php } ?>
		</center><?php
	}

	function printcommentbutton($pid)
	{
		if (!can_post($pid)) return;
		?>
		<form id=<?php echo("\"comment$pid\""); ?> action="io.php" method="POST" style="display: inline;">
			<input type="hidden" name="action" value="createcomment" />
			<input type="hidden" name="commentpid" value=<?php echo("\"$pid\""); ?> />
			<input type="submit" class="inlineButton" value="Comment" />
		</form>
		<?php
	}

	function printdeletebutton($pid)
	{
		if (!can_delete($pid)) return;
		?>
		<form id=<?php echo("\"delete$pid\""); ?> action="io.php" method="POST" style="display: inline;">
			<input type="hidden" name="action" value="deletepost" />
			<input type="hidden" name="postpid" value=<?php echo("\"$pid\""); ?> />
			<input type="submit" class="inlineButton" value="Delete" />
		</form>
		<?php
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

	function printeditbutton($pid)
	{
		if (!can_edit($_SESSION['userinfo']['uid'], $pid)) return;
		?>
		<form id=<?php echo("\"delete$pid\""); ?> action="io.php" method="POST" style="display: inline;">
			<input type="hidden" name="action" value="editpost" />
			<input type="hidden" name="postpid" value=<?php echo("\"$pid\""); ?> />
			<input type="submit" class="inlineButton" value="Edit" />
		</form>
		<?php
	}

	function printeditform($pid)
	{
		?>
		<div class="alone_rbox">
		<form action="io.php" method="POST">
			<input type="hidden" name="action" value="publishedit" />
			<input type="hidden" name="postpid" value=<?php echo("\"$pid\""); ?> />
			<textarea rows="4" cols="50" name="postmsg"><?php echo(getpostinfo($pid)['text']); ?></textarea>
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

	function printlogoutlink()
	{
		if (!isset($_SESSION['userinfo'])) return;
		?>|&nbsp;&nbsp;<a href="login.php">Log Out</a>&nbsp;&nbsp;<?php
	}

?>