
<?

function evenOdd_row($fields, $evenOdd) {
	if ($evenOdd) {
		$bgColor = "#88cccc";
	} else {
		$bgColor = "#88cccc";
	}
	$f0 = str_replace(" ", "&nbsp;", $fields[0]);
	$buf .= "<tr>";
	$buf .= "<td bgcolor=$bgColor valign=top align=left>$f0</td>";
	$buf .= "<td bgColor=$bgColor width=90% valign=top>$fields[1]</td>";
	$buf .= "</tr>";
	$buf .= "<tr>";
	$buf .= "<td bgcolor=$bgColor colspan=2 valign=top align=left>$fields[2]</td>";
	$buf .= "</tr>";

	return $buf;
}

function make_attrs($attrList) {
	if ($attrList) {
		if (!$attrList["size"]) {
			$attrList["size"] = 40;
		}
		foreach ($attrList as $attrKey => $attrVal) {
			$attrs .= "$attrKey=$attrVal ";
		}
	}
	return $attrs;
}

function make_fields($key, $arr) {
	$col1 = $arr["prompt"];
	if ($arr["choices"]) {
		$col2 = form_choice($key, $arr);
	} else if (!strcmp($arr["type"], "textarea")) {
		$col2 = form_textarea($key, $arr);
	} else {
		$col2 = form_input($key, $arr);
	}
	$col3 = $arr["desc"];
	return array($col1, $col2, $col3);
}

function form_textarea($key, $arr) {
	$attrs = make_attrs($arr["attrs"]);
	return "<textarea name=$key $attrs>$arr[value]</textarea>";
}

function form_input($key, $arr) {
	if ($arr["type"]) {
		$type = "type=$arr[type]";
	}
	$attrs = make_attrs($arr["attrs"]);

	return "<input $type name=$key value=\"$arr[value]\" $attrs>";
}

function form_choice($key, $arr) {
	$buf .= "<select name=$key>";
	foreach ($arr["choices"] as $choice) {
		$selected = "";
		if (!strcmp($choice, $arr["value"])) {
			$selected = "SELECTED";
		}
		$buf .= "<option $selected> $choice";
	}
	$buf .= "</select>";
	return $buf;
}

function locateDir($filename, $extraDir="", $ignorePath=0) {

	if (fs_file_exists("$extraDir/$filename")) {
		return $extraDir;
	}

	if ($ignorePath) {
		return;
	}

	foreach (explode(":", getenv("PATH")) as $path) {
		if (fs_file_exists("$path/$filename")) {
			return $path;
		}
	}
}

function locateFile($filename) {
	foreach (explode(":", getenv("PATH")) as $path) {
		if (fs_file_exists("$path/$filename")) {
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
	global $GALLERY_PHP_VALUE_OK; 

	/*
	 * the .htaccess file in the parent directory tries to
	 * auto_prepend the got-htaccess.php file.  If that worked, 
	 * then GALLERY_PHP_VALUE_OK will be set. 
	 */
	$success = array();
	$fail = array();
	if ($GALLERY_PHP_VALUE_OK) {
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
	} else if (!strcmp($version, "4.0RC1")) {
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
	global $GALLERY_REWRITE_OK;

	$success = array();
	$fail = array();
	if ($GALLERY_REWRITE_OK) {
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
		fs_executable("jpegtopnm"), 
		fs_executable("giftopnm"), 
		fs_executable("pngtopnm"), 
		fs_executable("ppmtojpeg"), 
		fs_executable("ppmtogif"), 
		fs_executable("pnmtopng"), 
		fs_executable("pnmscale"), 
		fs_executable("pnmfile"),
		fs_executable("ppmquant"),
		fs_executable("pnmcut"),
		fs_executable("pnmrotate"),
		fs_executable("pnmflip")
	);

	foreach ($netpbm as $bin) {
		if ($location) {
			$d = locateDir($bin, $location, 1);
		} else {
			$d = locateDir($bin, $gallery->app ? $gallery->app->pnmDir : "");
		}

		if (!$d) {
			$missing[$bin] = "Can't find <i>$bin</i>!";
		}

		if (!is_executable("$d/$bin")) {
			$missing[$bin] = "<i>$bin</i> is not executable!";
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
			if (fs_is_executable($file)) {
				fs_exec($file, $results, $status);
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

