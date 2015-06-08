<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>Timeline</title>
	</head>
<?php

	require_once('engine.php');
	require_once('ioengine.php');
	checklogin();
	echo("<header><div class=\"left\">Timeline</div>" .
		 "<div class=\"right\">");
	printpostbutton($_SESSION['userinfo']['uid']);
	printadminlink();
	printlogoutlink();
	echo("</div><br /></header><body>");
	display_timeline($_SESSION['userinfo']['uid']);

?>
	</body>
</html>