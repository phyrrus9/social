<?php

	require_once('engine.php');
	require_once('ioengine.php');
	require_once('admin_do_post.php');
	require_once('admin_do_user.php');

	/* MISC */

	function dosqlquery($recursed = 0, $initial = "")
	{
		if (!check_perm(ACCESS_ADMIN_SQL))
		{
			setresultbox("Not permitted to run SQL queries");
			return;
		}
		$remains = null;
		$query = $_POST['sqlqueryvalue'];
		if ($recursed == 1)
		{
			if (strlen($initial) < 5)
				return ""; //break out
			else
				$query = $initial;
		}
		$query_arr = explode(";", $query);
		$query = $query_arr[0];
		if (count($query_arr) > 1)
		{
			$remains = "";
			for ($i = 1; $i < count($query_arr); $i++)
				$remains .= $query_arr[$i] . ';';
			while (strstr($remains, ";;") != false)
				$remains = str_replace(";;", ";", $remains); //stupid empty query bug
		}
		$ret = "Query: $query<br />";
		$conn = common_connect();
		$res = sql_dquery($conn, $query);
		sql_disconnect($conn);
		$ret .= "<table border=\"1\"><tr>";
		if ($res == null)
		{
			$ret .= "<td>No data returned for query</td></tr>";
		}
		else
		{
			$keys = array_keys($res[0]);
			foreach($keys as $key)
				$ret .= "<th>$key</th>";
			$ret .= "</tr>";
			foreach($res as $arr)
			{
				$ret .= "<tr>";
				$values = array_values($arr);
				foreach($values as $value)
					$ret .= "<td>".htmlspecialchars($value)."</td>";
				$ret .= "</tr>";
			}
		}
		$ret .= "</table>" . "<br />" . dosqlquery(1, $remains);
		if ($recursed == 0)
			setresultbox($ret);
		else
			return $ret;
	}

?>