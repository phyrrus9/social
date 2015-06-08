<?php

	function cmp($a, $b)
	{
		if ($a['TIME'] ==  $b['TIME']) return 0;
    	return ($a['TIME'] < $b['TIME']) ? 1 : -1;
	}


	function dorecursivesort(&$arr)
	{
		usort($arr, "cmp");
		foreach($arr as $ent)
			if ($ent['REPLIES'] != NULL)
				dorecursivesort($ent['REPLIES']);
	}

?>