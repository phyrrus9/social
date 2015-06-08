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
		 "<div class=\"right\">
		 " . $_SESSION['userinfo']['name']
		 . "&nbsp;&nbsp;|&nbsp;");
	printpostbutton($_SESSION['userinfo']['uid']);
	printuserlink();
	printadminlink();
	printlogoutlink();
	echo("</div><br /></header><body>");
	display_timeline($_SESSION['userinfo']['uid']);

?>
	</body>
</html>