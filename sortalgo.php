<?php

	function timeline_cmp($a, $b)
	{
		if ($a['TIME'] < $b['TIME']) return -1;
		else if ($a['TIME'] > $b['TIME']) return 1;
		return 0;
	}

	function dorecursivesort(&$arr)
	{
		return;
		usort($arr, "timeline_cmp");
		foreach($arr as $ent)
			if ($ent['REPLIES'] != NULL)
				dorecursivesort($ent['REPLIES']);
	}

?>