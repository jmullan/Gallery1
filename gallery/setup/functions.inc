<?php /* $Id$ */ ?>
<?php

function evenOdd_row($fields, $evenOdd) {
	if ($evenOdd) {
		$bgColor = "#88cccc";
	} else {
		$bgColor = "#88cccc";
	}
	$f0 = str_replace(" ", "&nbsp;", $fields[0]);
	$buf .= "\n<tr>";
	$buf .= "\n\t<td bgcolor=$bgColor valign=top align=left>$f0</td>";
	$buf .= "\n\t<td bgColor=$bgColor width=90% valign=top>$fields[1]</td>";
	$buf .= "\n</tr>";
	$buf .= "\n<tr>";
	$buf .= "\n\t<td bgcolor=$bgColor colspan=2 valign=top align=left>$fields[2]</td>";
	$buf .= "\n</tr>\n";

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
	if (isset($arr["choices"])) {
		$col2 = form_choice($key, $arr);
	} else if (isset($arr["multiple_choices"])) {
		$col2 = form_multiple_choice($key, $arr);
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
	
	$buf = "\n\t<select name=$key>";
	foreach ($arr["choices"] as $choice => $value) {
		$selected = "";
		if (!strcmp($choice, $arr["value"])) {
			$selected = "SELECTED";
		}
		$buf .= "\n\t\t". '<option value="' . $choice . '" ' . $selected . '>'. $value . '</option>';
	}
	$buf .= "\n\t</select>\n";
	return $buf;
}

function form_multiple_choice($key, $arr) {
	
	$buf="";
	foreach ($arr["multiple_choices"] as $item => $value) {
		$selected = "";
		if (in_array($item, $arr["value"])) {
			$selected = "CHECKED";
		}
		$buf .= "\n\t<br><input name=\"${key}[]\" value=\"$item\" type=\"checkbox\" $selected>" . $value ;
	}
	return $buf;
}

function getPath() {

    /* Start with the server user's path */
    $path = explode(":", getenv("PATH"));

    /* Add in a few relatively obvious locations */
    $path[] = "/usr/local/gallery";
    $path[] = "/usr/local/gallery/bin";
    $path[] = "/usr/local/gallery/jhead";
    $path[] = "/usr/local/gallery/netpbm";
    $path[] = "/usr/local/bin";
    $path[] = "/usr/local/bin/jhead";
    $path[] = "/usr/local/bin/netpbm";
    $path[] = "/usr/local/netpbm";
    $path[] = "/usr/local/netpbm/bin";
    $path[] = "/usr/local/jhead";
    $path[] = "/usr/local/jhead/bin";
    $path[] = "/usr/bin/gallery";
    $path[] = "/usr/bin/gallery/jhead";
    $path[] = "/usr/bin/gallery/netpbm";

    return $path;
}

function locateDir($filename, $extraDir="", $ignorePath=0) {
    
	if (fs_file_exists("$extraDir/$filename")) {
		return $extraDir;
	}

	if ($ignorePath) {
		return;
	}

	foreach (getPath() as $path) {
		if (fs_file_exists("$path/$filename")) {
			return $path;
		}
	}
}

function locateFile($filename) {
	foreach (getPath() as $path) {
		if (fs_file_exists("$path/$filename")) {
			return "$path/$filename";
		}
	}
}

function one_constant($key, $value) {
	return "\$gallery->app->$key = \"{$value}\";\n";
}

function array_constant($key, $value) {
	$buf="";
	foreach ($value as $item) {
		$buf .= "\$gallery->app->${key}[] = \"{$item}\";\n";
	}
	return $buf;
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
	return error_format(_("Missing value").": <b>$desc</b>!");
}
function row_wrap($buf) {
	return "<tr><td><code>$buf</code></td></tr>";
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
		$success[] = _("I can read your .htaccess file");
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
		$success[] = sprintf(_("PHP v%s is OK"), phpversion());
	}

	return array($success, $fail);
}

function check_php4_1()
{
	global $MIN_PHP_MAJOR_VERSION;

	$success = array();
	$fail = array();

        $version = explode(".", phpversion());
        // $version = explode(".", "4.0.4");
        if ($version[0] < 4) {
		$fail["fail-too-old"]++;
        } elseif ($version[1] <1) {
		$fail["fail-too-old"]++;
        } else{
		$success[] = sprintf(_("PHP v%s is OK"), phpversion());
	}

	return array($success, $fail);
}
function check_mod_rewrite() 
{
	global $GALLERY_REWRITE_OK;

	$success = array();
	$fail = array();
	if ($GALLERY_REWRITE_OK) {
		$success[] = _("mod_rewrite is enabled");
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

	$bin = fs_executable("jhead");

	if ($location) {
		$d = locateDir($bin, $location);
	} else {
		$d = locateDir($bin, $gallery->app ? dirname($gallery->app->use_exif) : "");
	}
	if (!$d) {
		$fail["fail-exif"] = _("Could not locate jhead");
	} else {
		$success[] = _("jhead binary located");
	}

	return array($success, $fail);
}

function check_graphics($location="", $graphtool = "")
{
	global $gallery;

	$fail = array();
	$success = array();
	if (!strcmp($graphtool, "ImageMagick")) {
		$success[] = _("NetPBM not being used in this installation");
	    	return array($success, $fail);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $success[] = _("Cannot verify this path (it's not in your open_basedir list)");
	    return array($success, $fail);
	}

	$dirs = array();
	$missing = array();
	$netpbm = array(
		fs_executable("jpegtopnm"), 
		fs_executable("giftopnm"), 
		fs_executable("pngtopnm"), 
		fs_executable("pnmtojpeg"), 
		fs_executable("ppmtogif"), 
		fs_executable("pnmtopng"), 
		fs_executable("pnmscale"), 
		fs_executable("pnmfile"),
		fs_executable("ppmquant"),
		fs_executable("pnmcut"),
		fs_executable("pnmrotate"),
		fs_executable("pnmflip")
	);

	$fallback = array(
		fs_executable("pnmtojpeg") => fs_executable("ppmtojpeg")
	);

	foreach ($netpbm as $bin) {
		if ($location) {
			$d = locateDir($bin, $location, 1);
		} else {
			$d = locateDir($bin, $gallery->app ? $gallery->app->pnmDir : "");
		}

		/*
		 * If we can't find the primary file, look for the fallback file
		 * instead.
		 */
		if (!$d && $fallback[$bin]) {
			$newbin = $fallback[$bin];
			if ($location) {
				$d = locateDir($newbin, $location, 1);
			} else {
				$d = locateDir($newbin, $gallery->app ? $gallery->app->pnmDir : "");
			}
			if ($d) {
				$bin = $newbin;
			}
		}

		if (!$d) {
			$missing[$bin] = _("Can't find") ." <i>$bin</i>!";
		}

		if (!empty($d) && inOpenBasedir($d)) {
		    if (!fs_is_executable("$d/$bin")) {
			$missing[$bin] = "<i>$bin</i> ". _("is not executable") ."!";
		    }
		}
	}
	
	if (count($missing) == count($netpbm)) {
		$fail["fail-netpbm"]++;
	} else if (count($missing)) {
		$fail["fail-netpbm-partial"] = array_values($missing);
	} else {
		$success[] = sprintf(_("All %d NetPBM binaries located"),
				count($netpbm) );
	}

	return array($success, $fail);
}

function check_graphics_im($location="", $graphtool="")
{
	global $gallery;

	$fail = array();
	$success = array();

	if (!strcmp($graphtool, "NetPBM")) {
		$success[] = "ImageMagick not being used in this installation";
	    	return array($success, $fail);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $success[] = _("Cannot verify this path (it's not in your open_basedir list)");
	    return array($success, $fail);
	}

	$dirs = array();
	$missing = array();
	$imagick = array(
		fs_executable("identify"), 
		fs_executable("convert"),
	);

	foreach ($imagick as $bin) {
		if ($location) {
			$d = locateDir($bin, $location, 1);
		} else {
			$d = locateDir($bin, $gallery->app ? $gallery->app->ImPath : "");
		}

		if (!$d) {
			$missing[$bin] = _("Can't find") ." <i>$bin</i>!";
		}

		if (!empty($d) && inOpenBasedir($d)) {
		    if (!fs_is_executable("$d/$bin")) {
			$missing[$bin] = "<i>$bin</i> ". _("is not executable"). "!";
		    }
		}
	}
	
	if (count($missing) == count($imagick)) {
		$fail["fail-imagemagick"]++;
	} else if (count($missing)) {
		$fail["fail-imagemagick"] = array_values($missing);
	} else {
		$success[] = sprintf(_("All %d ImageMagick binaries located"),
				count($imagick));
	}

	return array($success, $fail);
}

function check_jpegtran($location="")
{
	global $gallery;

	$fail = array();
	$success = array();

	$bin = fs_executable("jpegtran");

	if ($location) {
		$d = locateDir($bin, $location);
	} else {
		$d = locateDir($bin, $gallery->app ? $gallery->app->pnmDir : "");
	}
	if (!$d) {
		$fail["fail-jpegtran"] = _("Could not locate jpegtran");
	} else {
		$success[] = _("jpegtran binary located");
	}

	return array($success, $fail);
}

function check_gettext()
{
	$fail = array();
	$success = array();
	if (in_array ("gettext", get_loaded_extensions()) && function_exists(gettext)) {
		$success[] = _("gettext installed");
	} else {
		$fail["fail-gettext"]++;
	}
	return array($success, $fail);
}
/*
function check_locale()
{

	$fail = array();
	$success = array();

	if (strncasecmp(PHP_OS,"win", 3)) {
		# Unix / Linux
		# Check which locales are installed
		if (is_readable("/etc/locale.gen")) {
			$list=split("\n",shell_exec('cat /etc/locale.gen | cut -d " " -f 1'));
		}
		if (sizeof($list) == 0 && is_readable("/usr/share/locale/")) {
			$list=split("\n",shell_exec('ls /usr/share/locale/'));
		}
		if (sizeof($list) == 0) {
			$list=split("\n", shell_exec("locale -a")) ;
		}
		if (sizeof($list) > 0) {
			$success[] = _("locale support found");
		} else {
			$fail["fail-locale"] = _("locale support not found");
		}
	}
	else {
		# Windows
		$fail["warning-locale"] = _("Locale support not determined");
		
	}
	return array($success, $fail);
}
*/

function check_gallery_locale()
{
	global $GALLERY_BASEDIR;
	$fail = array();
	$success = array();

	$locales=gallery_languages();
	if (sizeof($locales) == 0) {
		$fail["fail-gallery-locale"] = _("No languages found."); // should never occur!
	} else if (sizeof($locales) == 1) {
		$fail["fail-gallery-locale"] = _("Only US English version is available.  If you want other languages, download language packs from gallery site");
	} else {
		$success[] = sprintf(_("%d languages found"), sizeof($locales));
	}
	return array($success, $fail);
}

function check_absent_locales()
{
	global $GALLERY_BASEDIR;
	$fail = array();
	$success = array();

	$missing_locales=unavailable_locales();
	$avail_locales=available_locales();
	sort($missing_locales);
	sort($avail_locales);
	if (sizeof($missing_locales) == 0 ) {
		$success[] = _("All gallery locales are available on this host");
	} else if (sizeof($avail_locales) <= 1 || $missing_locales == $avail_locales) {
		$fail[] = "Only the default locale for this machine is available, so date format will not change for different languages.";
	} else {
		$msg = _("Dates in these languages may not be formatted properly, because the corresponding locales are missing:") . "<br>";
		$nls=getNLS();
		foreach ($missing_locales as $value) {
			$msg .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$msg .= $nls['languages'][$value];
			$msg .= " ($value)<br>";
		}
		$fail[] = $msg;
	}
	return array($success, $fail);
}

//returns all languages in this gallery installation

function gallery_languages() {
	$locale_dir = "../locale/";
	$nls=getNLS();
	$locales = array('en_US' => $nls['languages']['en_US']);
	if (is_dir($locale_dir)) {
		if ($dh = opendir($locale_dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != "CVS" && !ereg("^\.", $file)) {
					$locales[$file]=$nls['languages'][$file];
				}
			}
			closedir($dh);
		}
	}
	return $locales;
}

function test_locale($locale) {
	if (setlocale(LC_ALL, $locale)) {
		return true;
	}
	return false;
}
function all_locales ($set) {
	$list = array();
	$nls=getNLS();
	$gallery_languages=gallery_languages();
	$available_locales=array();
	$maybe_locales=array();
	$non_available_locales=array();

	$current_local=setlocale(LC_ALL, 0);
	$available_locales['en_US']=$nls['languages']['en_US'];

	if (strncasecmp(PHP_OS,"win", 3)) {
		# Unix / Linux
		# Check which locales are installed
		if (@is_readable("/etc/locale.gen")) {
			$list=array_merge($list, split("\n",shell_exec('cat /etc/locale.gen | cut -d " " -f 1')));
		}
		if (@is_readable("/usr/share/locale/")) {
			$list=array_merge($list, split("\n",shell_exec('ls /usr/share/locale/')));
		}
		$list=array_merge($list, split("\n", shell_exec("locale -a")) );
	}
	foreach ($gallery_languages as $locale => $locale_name) {
		if (in_array($locale,$nls['aliases'])) {
                        $keylist=array_keys($nls['aliases'],$locale);
                        $aliases = preg_grep ("/^(". substr($locale,0,2) 
						."|" . implode ("|", $keylist) 
						.")/",$list);
                } else {
                        $aliases =
				preg_grep ("/^". substr($locale,0,2) ."/",$list);
		}
		if (in_array($locale,$nls['aliases'])) {
                        $keylist=array_keys($nls['aliases'],$locale);
			foreach($keylist as $item) {
				if (setlocale(LC_ALL, $item)) {
					$aliases[]=$item;
				}
			}
                } 
		$aliases=array_filter(array_unique($aliases), test_locale);
		if (setlocale(LC_ALL, $locale)) {
			$available_locales[$locale]=$locale_name;
		} else if ($aliases) {
			$available_locales[$locale]=$locale_name;
			$maybe_locales[$locale]=$aliases;
		} else {
			$non_available_locales[]=$locale;
		}
	}
	setlocale(LC_ALL, $currrent_locale);
	switch ($set) {
		case 0:
			return $available_locales;
			break;
		case 1:
			return $maybe_locales;
			break;
		case 2:
			return $non_available_locales;
			break;
	}
}

function available_locales () {
	return all_locales(0);
}

function maybe_locales () {
	return all_locales(1);
}

function unavailable_locales () {
	return all_locales(2);
}

function vd($x, $string="") {
	print "\n$string <pre>";
	var_dump($x);
	print "</pre>\n";
}
function config_maybe_locales() {
	$results=array();
	$maybe=maybe_locales();
	$unavailable=unavailable_locales();
	$available=available_locales();
	$nls=getNLS();

        foreach ($maybe as $key => $aliases) {
		if (sizeof($aliases) < 1) {
			$unavailable[]=$key;
			continue;
		}
		if (sizeof($aliases) == 1) {
			$results["locale_alias[$key]"] = array (
				"type" => "hidden", 
				"value" => array_pop($aliases),
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]"
					);
			continue;
		}
		$index = $nls['languages'][$key] ;
		$choices=array();
		foreach ($aliases as $value) { 
			$choices[$value]=$value;
		}
		$results["locale_alias[$key]"] = array (
			"prompt" => sprintf(_("Possible locale problems with %s."),
				$nls['languages'][$key]),
			"desc" => _("This language is available, but there seems to be a locale problem.  Please select a locale for date format style.") .
				  "\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), " 1.3.5"). ")</b></font>",
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"choices" => $choices,
			"value" => $choices[0]);

			
        }
        foreach ($unavailable as $key) {
		if (sizeof($available) == 1) {
			$results["locale_alias[$key]"] = array (
				"type" => "hidden", 
				"value" => 0,
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]"
					);
			continue;
		}
		$index = $nls['languages'][$key] ;
		$choices=array();
		foreach ($available as $value) { 
			$choices[$value]=$value;
		}
		$results["locale_alias[$key]"] = array (
			"prompt" => sprintf(_("Locale problems with %s."),
				$nls['languages'][$key]),
			"desc" => _("This language is available, but we can't identified a suitable locale for it.  Please select a locale for date format style.") .
				  "\n\t<br><font color=blue><b>(". sprintf(_("this is new in version %s"), " 1.3.5"). ")</b></font>",
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"choices" => $choices,
			"value" => $choices[0]);

			
        }


	return $results;
}

function default_graphics()
{
	list ($imageMagick,) = check_graphics_im();
        
	if (count ($imageMagick)) {
		return "ImageMagick";
	} else {
		return "NetPBM";
	}
}

function check_safe_mode()
{
	$fail = array();
	$success = array();
	$safe_mode = ini_get("safe_mode");
	if (empty($safe_mode) ||
	    !strcasecmp($safe_mode, "off") ||
	    !strcasecmp($safe_mode, "0") ||
	    !strcasecmp($safe_mode, "false")) {
		$success[] = _("safe mode is off");
	} else {
		$fail["fail-safe-mode"]++;
	}
	return array($success, $fail);
}

function check_magic_quotes()
{
	$fail = array();
	$success = array();
	if (!get_magic_quotes_gpc()) {
		$success[] = _("magic quotes are off");
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

/*
 * Actually try to write to a file inside the directory.  This detects
 * open_basedir restrictions.
 */
function test_write_to_dir($dir) {
	$tmpfile = tempnam($dir, "dbg");
	if ($fd = fs_fopen($tmpfile, "w")) {
		fclose($fd);
		unlink($tmpfile);
		return 1;
	}

	return 0;
}

function inOpenBasedir($dir) {
    $openBasedir = ini_get('open_basedir');
    if (empty($openBasedir)) {
	return true;
    }

    /*
     * XXX: this is not perfect.  For example, if the open_basedir list
     * contains "/usr/localx" this code will match "/usr/local".  Let's not
     * worry too much about that now.
     */
    foreach (explode(':', $openBasedir) as $basedir) {
	if (!strncmp($basedir, $dir, strlen($basedir))) {
	    return true;
	}
    }
    
    return false;
}
?>
