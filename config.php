<?php


	$GLOBALS['SQL_SERVER'] = "localhost";
	$GLOBALS['SQL_USER']   = "root";
	$GLOBALS['SQL_PASS']   = "alpine";
	$GLOBALS['SQL_DB']     = "social"; //fuck this
	
	$GLOBALS['SETTINGS'] =
		array(
				'NAME' => 'rabba',
				'BASE' => 'http://preview.ybh2576qtexko6r5gk605lwsqlx47vi5b9mqd5aw8my4x6r.box.codeanywhere.com',
				'VERSION' => 'v0.2',
				'ADMIN'=>
					array(
						'NAME' => 'Ethan Laur',
						'EMAIL'=> 'phyrrus9@gmail.com'
					),
				'DEFAULTS'=>
					array(
						'ACCESS' => 1,
						'DISPLAY_NAME' => 'New User'
					),
				'OPTIONS'=>
					array(
						'ALLOW_USER_REGISTRATION' => false,
						'DYNAMIC_WALLPAPER_CHANGE'=> false
					)
			);
	function GET_SETTING($key)
		{ return $GLOBALS['SETTINGS'][$key]; }
	function GET_DEFAULT($key)
		{ return GET_SETTING('DEFAULTS')[$key]; }
	function GET_OPTION($key)
		{ return GET_SETTING('OPTIONS')[$key]; }

?>