<?php
	/* wallhaven_random.php copyright (c) 2015 Ethan Laur (phyrrus9)
	 * this script will return a random image from wallhaven.cc
	 */

	$random_base = "http://alpha.wallhaven.cc/random";
	$regex = "http://alpha.wallhaven.cc/wallpapers/thumb/small/th-%d.jpg"; //followed by a number, then .jpg
	$full_base = "http://alpha.wallhaven.cc/wallpapers/full/wallhaven-"; //followed by img_number then .jpg
	$img_ext = ".jpg";

	$tmp = file_get_contents($random_base);
	$img_idlist = array();
	if ($c=preg_match_all ("/.*?data-src=\"((?:http|https)(?::\\/{2}[\\w]+)(?:[\\/|\\.]?)(?:[^\\s\"]*))/is", $tmp, $matches))
		foreach($matches[1] as $match)
				array_push($img_idlist, extract_id($match));
	$chosen_id = $img_idlist[rand(0, count($img_idlist))];
	$url = $full_base . $chosen_id . $img_ext;
	$fname = tempnam("/tmp", "IMG");
	set_time_limit(0);
	$fp = fopen ($fname, 'w');
	$ch = curl_init(str_replace(" ","%20",$url));
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	header("Content-Type: image/png");
	header("Content-Length: " . filesize($fname));
	readfile($fname);
	unlink($fname);

	function extract_id($str)
	{
		$id = 0;
		global $regex;
		sscanf($str, $regex, $id);
		return $id;
	}

?>