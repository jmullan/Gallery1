<?php
if (!isset ($included)) {
	die ('Must be included');
}

function dbQuery ($query, $args = false) {
	//I thought about this for a while, but I can't think of a better way.
	//Feel free to change it.  It works, and I think it's the best way
	if ($args) {
		$pos = 0;
		$i = 0;
		$count = strpos (substr ($query, $pos), '??');
		while ($count !== false) {
			$tmp = mysql_escape_string ($args[$i]);
			$query = substr_replace ($query, $tmp, $count + $pos, 2);

			$pos += $count + strlen ($tmp);

			$count = strpos (substr ($query, $pos), '??');
			$i++;
		}
	}

	$result = mysql_query ($query);
	
	if (!$result) {
		throwError (mysql_error());
	}
	
	return $result;
}

?>
