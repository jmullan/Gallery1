
<?
function row_text($key, $arr) {
	if (strcmp($arr["type"], "hidden")) {
		$buf = "<tr><td width=75% valign=top align=right>$arr[desc]</td><td valign=top>";
	} else {
		$buf = "<tr><td width=75% valign=top align=right>&nbsp;</td><td valign=top>";
	}
	if ($arr["type"]) {
		$type = "type=$arr[type]";
	}
	$buf .= "<input $type name=$key value=\"$arr[value]\" size=40>";
	$buf .= "</td></tr>";
	return $buf;
}

function row_choice($key, $arr) {
	$buf = "<tr><td valign=top align=right>$arr[desc]</td><td valign=top>";
	$buf .= "<select name=$key>";
	foreach ($arr["choices"] as $choice) {
		$selected = "";
		if (!strcmp($choice, $arr["value"])) {
			$selected = "SELECTED";
		}
		$buf .= "<option $selected> $choice";
	}
	$buf .= "</select>";
	$buf .= "</td></tr>";
	return $buf;
}

function locateDir($filename, $extraDir="") {

	foreach (explode(":", getenv("PATH")) as $path) {
		if (file_exists("$path/$filename")) {
			return $path;
		}
	}

	if (file_exists("$extraDir/$filename")) {
		return $extraDir;
	}
}

function locateFile($filename) {
	foreach (explode(":", getenv("PATH")) as $path) {
		if (file_exists("$path/$filename")) {
			return "$path/$filename";
		}
	}
}

function one_constant($key, $value) {
	return "\$app->$key = \"$value\";\n";
}

function defaults($key, $value) {
	return "\$app->default[\"$key\"] = \"$value\";\n";
}

function use_feature($feature) {
	return "\$app->feature[\"$feature\"] = 1;\n";
}
function no_feature($feature, $cause) {
	return "\$app->feature[\"$feature\"] = 0; // ($cause)\n";
}
function error_missing($desc) {
	return error("Missing value: <b>$desc</b>!");
}
function row_wrap($buf) {
	return "<tr><td><code>$buf</td></tr>";
}

function check_htaccess()
{
	global $GALLERY_GOT_HTACCESS; 

	/*
	 * the .htaccess file in the parent directory tries to
	 * auto_prepend the got-htaccess.php file.  If that worked, 
	 * then GALLERY_GOT_HTACCESS will be set. 
	 */
	$success = array();
	$fail = array();
	if (isset($GALLERY_GOT_HTACCESS)) {
		$success[] = "I can read your .htaccess file";
	} else {
		$fail["fail-htaccess"]++;
	}

	return array($success, $fail);
}

function check_php()
{
	global $MIN_PHP_MAJOR_VERSION;

	$version = phpversion();
	$success = array();
	$fail = array();
	if (floor($version) < floor($MIN_PHP_MAJOR_VERSION)) {
		$fail["fail-too-old"]++;
	} else if (!strncmp($version, "4.0.3", 5)) {
		$fail["fail-incompatible"]++;
	} else {
		$success[] = "PHP v" . phpversion() . " is OK";
	}

	return array($success, $fail);
}

function check_mod_rewrite() 
{
	global $GALLERY_REWRITE;

	$success = array();
	$fail = array();
	if ($GALLERY_REWRITE) {
		$success[] = "";
	} else {
		$fail["fail-mod-rewrite"]++;
	}

	return array($success, $fail);
}

function check_graphics()
{
	global $app;

	$fail = array();
	$success = array();

	$dirs = array();
	$missing = array();
	$netpbm = array(
		"jpegtopnm", 
		"giftopnm", 
		"pngtopnm", 
		"ppmtojpeg", 
		"pnmscale", 
		"pnmfile",
		"pnmrotate",
	);

	foreach ($netpbm as $bin) {
		$d = locateDir($bin, $app ? $app->pnmDir : "");
		if (!$d) {
			$missing[$bin] = "Can't find <i>$bin</i>!";
		}
	}
	
	if (count($missing) == count($netpbm)) {
		$fail["fail-netpbm"]++;
	} else if (count($missing)) {
		$fail["fail-netpbm-partial"] = array_values($missing);
	} else {
		$success[] = "";
	}

	return array($success, $fail);
}

function check_magic_quotes()
{
	$fail = array();
	$success = array();
	if (!get_magic_quotes_gpc()) {
		$success[] = "";
	} else {
		$fail[] = "fail-magic-quotes";
	}

	return array($success, $fail);
}

function makePathWrapper($path, $app)
{
	return "/bin/csh -c &#039;(setenv PATH $path:" . 
		getenv("PATH") . 
		" ; $app %s)&#039;";
}

?>

