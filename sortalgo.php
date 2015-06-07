<?php

	function sort_array_swap($arr, $first, $second)
	{
		$tmp = $arr[$first];
		$arr[$first] = $arr[$second];
		$arr[$second] = $first;
	}

	function sort_array_asc($arr, $key)
	{
		$size = count($arr);
		for ($i = 0; $i < $size; $i++)
			for ($j = 0; $j < $size - $i - 1; $j++)
			{
				if ($key == null)
					if ($arr[$i] > $arr[$i + 1])
						sort_array_swap($arr, $i, $i + 1);
				else
					if ($arr[$i][$$key] > $arr[$i + 1][$$key])
						sort_array_swap($arr, $i, $i + 1);
			}
	}

?>