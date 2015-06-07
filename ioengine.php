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
				<li>
					<a class="hiddenanchor" name=<?php echo("\"pid" . $tpid . "\""); ?>
					   id=<?php echo("\"pid" . $tpid . "\""); ?>/>
					<?php echo($thread['NAME'] . " | " . $thread['TIME']); ?><br />
					<hr>
					<?php echo($thread['TEXT']); ?>
					<form id=<?php echo("\"comment$tpid\""); ?> action="io.php" method="POST" style="display: inline;">
						<input type="hidden" name="action" value="createcomment" />
						<input type="hidden" name="commentpid" value=<?php echo("\"$tpid\""); ?> />
						<input type="submit" class="commentButton" value="Comment" />
					</form>
				</li>	
			<?php
			if ($thread['REPLIES'] != NULL)
				subtimeline($thread['REPLIES']); //gotta love mixed-language recursion
		}
		echo("</ul>");
	}

	function printcommentform($pid)
	{
		?>
		<form action="io.php" method="POST">
			<input type="hidden" name="action" value="postcomment" />
			<input type="hidden" name="commentpid" value=<?php echo("\"$pid\""); ?> />
			<textarea rows="4" cols="50" name="commenttext"></textarea>
			<input type="submit" />
		</form>
		<?php
	}

?>