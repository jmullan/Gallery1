
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

	if (file_exists("$extraDir/$filename")) {
		return $extraDir;
	}

	foreach (explode(":", getenv("PATH")) as $path) {
		if (file_exists("$path/$filename")) {
			return $path;
		}
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
	return "\$gallery->app->$key = \"$value\";\n";
}

function defaults($key, $value) {
	return "\$gallery->app->default[\"$key\"] = \"$value\";\n";
}

function use_feature($feature) {
	return "\$gallery->app->feature[\"$feature\"] = 1;\n";
}
function no_feature($feature, $cause) {
	return "\$gallery->app->feature[\"$feature\"] = 0; // ($cause)\n";
}
function error_missing($desc) {
	return error_format("Missing value: <b>$desc</b>!");
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
	} else if (!strcmp($version, "4.0.0")) {
		$fail["fail-incompatible"]++;
	} else if (!strcmp($version, "4.0.2")) {
		$fail["fail-incompatible"]++;
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
		$success[] = "mod_rewrite is enabled";
	} else {
		$fail["fail-mod-rewrite"]++;
	}

	return array($success, $fail);
}

function check_exif($location="")
{
	global $gallery;

	$fail = array();
	$success = array();

	$bin = "jhead";

    if ($location) {
		$d = locateDir(bin, $location);
	} else {
		$d = locateDir($bin, $gallery->app ? $gallery->app->pnmDir : "");
	}
	if (!$d) {
		$fail["fail-exif"] = "Could not locate jhead";
	} else {
		$success[] = "jhead binary located";
	}

	return array($success, $fail);
}

function check_graphics($location="")
{
	global $gallery;

	$fail = array();
	$success = array();

	$dirs = array();
	$missing = array();
	$netpbm = array(
		"jpegtopnm", 
		"giftopnm", 
		"pngtopnm", 
		"ppmtojpeg", 
		"ppmtogif", 
		"pnmtopng", 
		"pnmscale", 
		"pnmfile",
		"ppmquant",
		"pnmcut",
		"pnmrotate",
		"pnmflip"
	);

	foreach ($netpbm as $bin) {
		if ($location) {
			$d = locateDir($bin, $location);
		} else {
			$d = locateDir($bin, $gallery->app ? $gallery->app->pnmDir : "");
		}
		if (!$d) {
			$missing[$bin] = "Can't find <i>$bin</i>!";
		}
	}
	
	if (count($missing) == count($netpbm)) {
		$fail["fail-netpbm"]++;
	} else if (count($missing)) {
		$fail["fail-netpbm-partial"] = array_values($missing);
	} else {
		$success[] = "All " . count($netpbm) . " NetPBM binaries located";
	}

	return array($success, $fail);
}

function check_magic_quotes()
{
	$fail = array();
	$success = array();
	if (!get_magic_quotes_gpc()) {
		$success[] = "magic quotes are off";
	} else {
		$fail["fail-magic-quotes"]++;
	}

	return array($success, $fail);
}

function detect_exec_status() 
{
	global $gallery;

	if ($gallery->app && isset($gallery->app->expectedExecStatus)) {
		return $gallery->app->expectedExecStatus;
	}

	// If PHP is compiled with the --enable-sigchild option, then every 
	// exec() call returns an error status of -1.  WTF?!?!  Sigh.  So
	// Let's do some checking on some pretty standard programs and see
	// what they return.
	$progs = array(
		"ls",
		"echo",
		"hostname",
		"pwd",
		"df",
		"ps",
		"sync",
	);

	foreach ($progs as $prog) {
		$dir = locateDir($prog);
		if ($dir) {
			$file = "$dir/$prog";
			if (is_executable($file)) {
				exec($file, $results, $status);
				$count[$status]++;
			} 
		}
	}

	if (count($count) == 0) {
		// Nothing!  :-(  Hope for the best.
		return 0;
	} else {
		// Return the one that we see the most of.
		$max = -1;
		foreach ($count as $key => $val) {
			if ($val > $max) {
				$status = $key;
				$max = $val;
			}
		}
	}

	return $status;
}
?>

