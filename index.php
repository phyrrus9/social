<html>

	<link rel="stylesheet" href="style.css" />
	
<?php

	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();
?>
	<form action="io.php" method="POST">
		<input type="hidden" name="action" value="createcomment" />
		<input type="hidden" name="commentpid" value="0" />
		<input type="submit" class="newPostButton" value="New Post" style="display: inline;" />
	</form>
<?php
	display_timeline($_SESSION['userinfo']['uid']);

?>
	
</html>