<?php /* $Id$ */ ?>
<?php

function evenOdd_row($fields, $evenOdd) {
	$buf='';

	$f0 = str_replace(" ", "&nbsp;", $fields[0]);
	if ($fields[3] == "block_element") {
		$buf .= "\n<tr><td class=\"shortdesc\">$f0</td><td class=\"shortdesc\">".$fields[1]."</td></tr>";
	} else if ($fields[3] == "block_start") {
		$buf .= "\n<tr><td class=\"shortdesc\" colspan=2 valign=top align=left><table>";
		$buf .= "\n\t<td class=\"content\" align=\"left\" valign=\"top\">$f0<p>";
		$buf .= "\n\t".$fields[2]."</td>";
		$buf .= "\n<td><table >";
	} else if ($fields[3] == "block_end") {
		$buf .= "\n</table></td></table></td></tr>";
	} else {
		$buf .= "\n<tr>";
		$buf .= "\n\t<td class=\"shortdesc\" valign=top align=left>$f0</td>";
		$buf .= "\n\t<td class=\"shortdesc\" width=90% valign=top>$fields[1]</td>";
		$buf .= "\n</tr>";
		$buf .= "\n<tr>";
		$buf .= "\n\t<td class=\"desc\" colspan=2 valign=top align=left>$fields[2]</td>";
		$buf .= "\n</tr>\n";
	}

	return $buf;
}

function make_attrs($attrList) {
	$attrs = '';
	if ($attrList) {
		if (!isset($attrList["size"])) {
			$attrList["size"] = 40;
		}
		foreach ($attrList as $attrKey => $attrVal) {
			$attrs .= "$attrKey=$attrVal ";
		}
	}
	return $attrs;
}

function make_fields($key, $arr) {
	if (isset($arr["prompt"])) {
		$col1 = $arr["prompt"];
	} else {
		$col1 = "";
	}
	if (isset($arr["type"]) && $arr["type"] == "hidden") {
		$col2 = form_input($key, $arr);
	} else if (isset($arr["choices"])) {
		$col2 = form_choice($key, $arr);
	} else if (isset($arr["multiple_choices"])) {
		$col2 = form_multiple_choice($key, $arr);
	} else if (!strcmp($arr["type"], "textarea")) {
		$col2 = form_textarea($key, $arr);
	} else if (isset($arr["type"]) && $arr["type"] == "text") {
		$col2 = form_input($key, $arr);
	} else if (isset($arr["type"]) && $arr["type"] == "nv_pairs") {
		$col2 = form_nv_pairs($key, $arr);
	} else {
		$col2 ="";
	}
	if (isset($arr["desc"])) {
		$col3 = $arr["desc"];
	} else {
		$col3 = "";
	}
	$col4 = isset($arr["type"]) ?  $arr["type"] : NULL;
	return array($col1, $col2, $col3,$col4);
}

function form_textarea($key, $arr) {
	$attrs = make_attrs($arr["attrs"]);
	return "<textarea name=$key $attrs>$arr[value]</textarea>";
}

function form_input($key, $arr) {
	if ($arr["type"]) {
		$type = "type=$arr[type]";
	}
	if (isset($arr["attrs"])) {
		$attrs = make_attrs($arr["attrs"]);
	} else {
		$attrs = '';
	}

	return "<input $type name=$key value=\"$arr[value]\" $attrs>";
}

function form_nv_pairs($key, $arr) {
	if (isset($arr["attrs"])) {
		$attrs = make_attrs($arr["attrs"]);
	} else {
		$attrs = '';
	}
	$x=0;
	$buf="\n<table><tr><td><b>"._("Name")."</b></td><td><b>"._("Value")."</b></td></tr>";
	foreach ($arr["value"] as $result) {
		$name=$result["name"];
		$value=$result["value"];
		$buf .= '<tr><td><input type="text" name="' . $key ."[$x][name]\" value=\"$name\" $attrs></td>\n";
		$buf .= '<td><input type="text" name="' . $key . "[$x][value]\" value=\"$value\" $attrs></td></tr>\n";
		$x++;
		if ($x >= $arr["size"]) {
			break;
		}
	}
	for (; $x<$arr["size"]; $x++) {
		$buf .= '<tr><td><input type="text" name="' . $key ."[$x][name]\" $attrs></td>\n";
		$buf .= '<td><input type="text" name="' . $key ."[$x][value]\" $attrs></td></tr>\n";
	}
	$buf.="</table>";
	return $buf;
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
	
	$buf= '<table><tr><td valign="top">';
	$count=0;
	foreach ($arr["multiple_choices"] as $item => $value) {
		if ($count%15 ==0) {
			$buf .= "</td>\n<td valign=\"top\">";
		}
		$count++;
		$selected = "";
		if (in_array($item, $arr["value"])) {
			$selected = "CHECKED";
		}
		$buf .= "\n\t<br><input name=\"${key}[]\" value=\"$item\" type=\"checkbox\" $selected>" . $value ;
	}
	$buf .="</td></tr></table>";
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
function error_missing($desc, $key) {
	if (empty($desc)) {
		$desc=$key;
	}
	return error_format(_("Missing value").": <b>$desc</b>!<br>");
}
function row_wrap($buf) {
	return "<code>$buf</code><br>";
}

function error_row_wrap($buf) {
	return "</pre>$buf</pre><br>";
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
		$fail["fail-htaccess"] = 1;
	}

	return array($success, $fail);
}

function check_php()
{
	global $MIN_PHP_MAJOR_VERSION;

	$version = phpversion();
        $version_ml = explode(".", $version);
	$success = array();
	$fail = array();
	$warn = array();

	if (floor($version) < floor($MIN_PHP_MAJOR_VERSION)) {
		$fail["fail-too-old"] = 1;
	} else if (!strcmp($version, "4.0.0")) {
		$fail["fail-incompatible"] = 1;
	} else if (!strcmp($version, "4.0RC1")) {
		$fail["fail-incompatible"] = 1;
	} else if (!strncmp($version, "4.0.3", 5)) {
		$fail["fail-incompatible"] = 1;
        } elseif ($version_ml[0] == 4 && $version_ml[1] < 1) {
		$warn["warn-too-old-ml"] = 1;
        } else {
		$success[] = sprintf(_("PHP v%s is OK"), phpversion());
	}

	return array($success, $fail, $warn);
}
function check_mod_rewrite() 
{
	global $GALLERY_REWRITE_OK;

	$success = array();
	$fail = array();
	if ($GALLERY_REWRITE_OK) {
		$success[] = _("mod_rewrite is enabled");
	} else {
		$fail["fail-mod-rewrite"] = 1;
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
		$d = locateDir($bin, isset($gallery->app->use_exif) ? dirname($gallery->app->use_exif) : "");
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
			$d = locateDir($bin, isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : "");
		}

		/*
		 * If we can't find the primary file, look for the fallback file
		 * instead.
		 */
		if (!$d && isset($fallback[$bin])) {
			$newbin = $fallback[$bin];
			if ($location) {
				$d = locateDir($newbin, $location, 1);
			} else {
				$d = locateDir($newbin, isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : "");
			}
			if ($d) {
				$bin = $newbin;
			}
		}

		if (!$d) {
			$missing[$bin] = sprintf(_("Can't find %s!"), "<i>$bin</i>");
		}

		if (!empty($d) && inOpenBasedir($d)) {
		    if (!fs_is_executable("$d/$bin")) {
			$missing[$bin] = sprintf(_("%s is not executable!"),
					"<i>$bin</i> "); 
		    }
		}
	}
	
	if (count($missing) == count($netpbm)) {
		$fail["fail-netpbm"] = 1;
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
			$d = locateDir($bin, isset($gallery->app->ImPath) ? $gallery->app->ImPath : "");
		}

		if (!$d) {
			$missing[$bin] = sprintf(_("Can't find %s!"), "<i>$bin</i>");
		}

		if (!empty($d) && inOpenBasedir($d)) {
		    if (!fs_is_executable("$d/$bin")) {
			$missing[$bin] = sprintf(_("%s is not executable!"),
					"<i>$bin</i> "); 
		    }
		}
	}
	
	if (count($missing) == count($imagick)) {
		$fail["fail-imagemagick"] = 1;
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
		$d = locateDir($bin, isset($gallery->app->pnmDir) ? $gallery->app->pnmDir : "");
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
	if (in_array ("gettext", get_loaded_extensions()) && function_exists('gettext')) {
		$success[] = _("gettext installed");
	} else {
		$fail["fail-gettext"] = 1;
	}
	return array($success, $fail);
}

function check_gallery_languages()
{
	global $GALLERY_BASEDIR;
	global $gallery;
	$fail = array();
	$success = array();
	$nls = getNLS();

	$languages=gallery_languages();
	if (sizeof($languages) == 0) {
		$fail["fail-gallery-languages"] = _("No languages found."); // should never occur!
	} else if (sizeof($languages) < sizeof($nls['language'])) {
		$fail["fail-gallery-languages"] 
			= sprintf(_("Only %d of %d possible languages are available.  If you want the missing languages, download a fresh copy of %s from %s."),
					sizeof($languages),
					sizeof($nls['language']),
					Gallery(),
					"<a href=\"$gallery->url\">$gallery->url</a>");
	} else {
		$success[] = sprintf(_("All %d languages found"), sizeof($languages));
	}
	return array($success, $fail);
}

function check_gallery_version()
{
	global $GALLERY_BASEDIR;
	global $gallery;
	$fail = array();
	$success = array();

	/* how many days old is the gallery version? */
	$age = (time() - $gallery->last_change)/86400;

	/* is this a beta or RC version? */
	$beta = ereg('-(b|RC)[0-9]*$', $gallery->version);

	$link="<a href=\"$gallery->url\">$gallery->url</a>";

	$visit=sprintf(_("You can check for more recent versions by visiting %s."), 
			$link);
	$this_version = sprintf(_("This version of %s was released on %s."),
			Gallery(), strftime("%x", $gallery->last_change));
	$this_beta_version = sprintf(_("This is a development build of %s that was released on %s."),
			Gallery(), strftime("%x", $gallery->last_change));

	if ($age > 180) {
		$fail["too_old"] = "$this_version  $visit";
	} else if ($age > 14 && $beta) {
		$fail["too_old"] = "$this_beta_version  $visit";
	} else if ($beta) {
		$success["ok"] = "$this_beta_version  $visit" . "  "  
			. _("Please check regularly for updates.");
	} else {
		$success["ok"] = "$this_version  $visit";
	}
	return array($success, $fail);
}

function check_absent_locales()
{
	global $GALLERY_BASEDIR;
	global $locale_check;
	$fail = array();
	$success = array();

	$locales=$locale_check;

	if($locales != NULL && sizeof($locales["unavailable_locales"]) ==0) {
		$success[] = _("All gallery locales are available on this host");
	} else if(sizeof($locales["available_locales"]) == 0) {
		if (ini_get('open_basedir') && getOS() != OS_LINUX) {
			$fail[] = sprintf(_("We were unable to detect any locales.  However, your server is configured with the %s restriction so this may be interfering with the way that we detect locales.  Unfortunately this means the date format will not change for different languages.  However, it is ok to continue."),
				'<a href="http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir">open_basedir</a>');
	    } else {
			if (getOS() == OS_LINUX) {
				$fail[] = sprintf(_("We were unable to detect any system locales. Multilanguage functions will be disabled. Please install the corresponding locales or ask your Provider to do this. This problem is known on %s systems. In this case please have a look at this %sDebian locale HowTo%s"),"Debian", '<a href="http://people.debian.org/~schultmc/locales.html">', "</a>");
			} else {
				$fail[] = _("Only the default locale for this machine is available, so date format will not change for different languages.");
	    }
		}
	} else {
		$msg = _("Dates in these languages may not be formatted properly, because the corresponding locales are missing:") . "<br>";
		$nls=getNLS();
		foreach ($locales["unavailable_locales"] as $value) {
			$msg .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$msg .= $nls['language'][$value];
			$msg .= " ($value)<br>";
		}
		$fail[] = $msg;
	}
	return array($success, $fail);
}

//returns all languages in this gallery installation

function gallery_languages() {
	$nls=getNLS();
	$locales = array('en_US' => $nls['language']['en_US']);
	foreach ($nls['language'] as $key => $value) {
		if (language_exists($key)) {
			$locales[$key]=$nls['language'][$key];
		}
	}
	return $locales;
}

function test_locale($locale) {
	if (setlocale(LC_ALL, $locale === 'null' ? '' : $locale)) {
		return true;
	}
	return false;
}
function check_locale() {
	$nls=getNLS();
	$gallery_languages=gallery_languages();
	$system_locales=array();

	$available_locales=array();
	$maybe_locales=array();
	$unavailable_locales=array();

	/* Lets see which system locales are installed. */
	if (getOS() != OS_WINDOWS) { 
		# Unix / Linux
		# Check which locales are installed
		$tmp=explode("\n", shell_exec("locale -a"));
		if(count($tmp) >3) {
			$system_locales=$tmp;
		} elseif (@is_readable("/etc/locale.gen")) {
			$system_locales=explode("\n",shell_exec('grep -v -e "^#" /etc/locale.gen | cut -d " " -f 1'));
		} elseif (@is_readable("/usr/share/locale/")) {
			$system_locales=array_merge($system_locales, explode("\n",shell_exec('ls /usr/share/locale/')));
		}
	}


	/* DAMN, there are none we use Linux and our PHP uses gettext*/
	if(     (sizeof($system_locales) == 0 ) &&
		(getOS() == OS_LINUX ) &&
		(in_array("gettext", get_loaded_extensions() ) && function_exists('gettext') )
	) {
		return NULL;
	}

	/* There were at least one system locale
	** Now lets test if our languages are supported by the system
	*/
	
	foreach ($gallery_languages as $locale => $locale_name) {
		$aliases=array();
		
		/* Found an supported one, put it in availables */
		if ( (in_array($locale, $system_locales)) || (setlocale(LC_ALL, $locale === 'null' ? '' : $locale))) {
			$available_locales[$locale]=$locale;
			continue;
		}
		
		/* we use the first 2 letter to build an alias list
		**  e.G. nl to find nl_BE or nl_NL
		*/
		if (in_array($locale,$nls['alias'])) {
			$keylist = array_keys($nls['alias'],$locale);
			$aliases = $keylist;
			if (getOS() != OS_WINDOWS) {
                                foreach ($system_locales as $key => $value) {
                                        if (ereg('^(' . substr($locale,0,2) . '|' . implode('|', $keylist) . ')', $value)) {
                                                $aliases[$key] = $value;
                                        }
                                }
			}
		} else {
                        foreach ($system_locales as $key => $value) {
                                if (ereg('^' . substr($locale,0,2), $value)) {
                                        $aliases[$key] = $value;
                                }
                        }
		}

		if ($aliases) {
			foreach ($aliases as $test) {
				if (setlocale(LC_ALL,$test === 'null' ? '' : $test)) {
					$maybe_locales[$locale][]=$test;
				}
			}
			if (! isset($maybe_locales[$locale])) {
                                $unavailable_locales[] = $locale;
                        }
		} else {
			$unavailable_locales[]=$locale;
		}
	}

	return array(
		"available_locales" => $available_locales,
		"maybe_locales" => $maybe_locales,
		"unavailable_locales" => $unavailable_locales
	);

}


function config_maybe_locales() {
	global $locale_check;

	$results=array();
	$locales=$locale_check;
	$available=$locales["available_locales"];
	$maybe=$locales["maybe_locales"];
	$unavailable=$locales["unavailable_locales"];

	if($locales == NULL) return;

	$nls=getNLS();

	$block_start_done=false;
	
        foreach ($maybe as $key => $aliases) {
		if (sizeof($aliases) < 1) {
			$unavailable[]=$key;
			continue;
		}
		if (sizeof($aliases) == 1) {
			$results["locale_alias['$key']"] = array (
				"type" => "hidden", 
				"value" => array_pop($aliases),
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]"
					);
			continue;
		}
		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b> ".sprintf(_("Locale Selection required")),
					"desc" => _("There is more than one suitable locale installed on your machine for the following languages.  Please chose the one you think is most suitable"));
		}
		$index = $nls['language'][$key] ;
		$choices=array();
		foreach ($aliases as $value) { 
			$choices[$value]=$value;
		}
		$results["locale_alias['$key']"] = array (
			"prompt" => $nls['language'][$key],
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"type" => "block_element",
			"choices" => $choices,
			"value" => current($choices));

			
        }
	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
	}
	$block_start_done=false;
	$choices=array();
	foreach ($available as $choice => $value) { 
		$choices[$choice]=$nls['language'][$value];
	}

	$avail_keys=array_keys($available);
        foreach ($unavailable as $key) {
		if (sizeof($available) <= 1) {
			$value = $avail_keys[0] ? $avail_keys[0] : 'null';
			$results["locale_alias['$key']"] = array (
				"type" => "hidden", 
				"value" => $value,
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]"
					);
			continue;
		}
		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b> ".sprintf(_("Locale problems")),
					"desc" => _("There are no apparently suitable locales installed on your machine for the following languages.  Please choose the one you think is most suitable."));
		}
		$index = $nls['language'][$key] ;
		$results["locale_alias['$key']"] = array (
			"prompt" => $nls['language'][$key],
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"type" => "block_element",
			"choices" => $choices,
			"value" => current($choices));

			
        }
	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
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
		$fail["fail-safe-mode"] = 1;
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
		$fail["fail-magic-quotes"] = 1;
	}

	return array($success, $fail);
}

function check_poll_nv_pairs($var) {
	$fail = array();
	$success = array();
	$finished = false;
	$rownum=0;
	foreach ($var as $element) {
		$rownum++;
		if (!$element["name"]) {
			$finished=true;
			if ($element["value"]) {
				$fail[]=sprintf(_("In %s, missing %s in row %d with %s %s."),
					_("Vote words and values"), 
					_("Name"), $rownum, _("Value"), 
					$element["value"]);
				break;
			}
			continue;
		} else {
			if ($finished) {
				$fail[]=sprintf(_("In %s, blank in row %d."),
					_("Vote words and values"), 
					$rownum-1); 
				break;
			} else if (!ereg("^[1-9][0-9]*$", $element["value"])) {
				$fail[]=sprintf(_("In %s, for name %s (row %d) value %s should be a positive whole number"), 
					_("Vote words and values"), 
					$element["name"], 
					$rownum, $element["value"]);
				break;
			}
		}
	}
	return array($success, $fail);
}
function detect_exec_status() 
{
	global $gallery;

	if (isset($gallery->app) && isset($gallery->app->expectedExecStatus)) {
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

	$count = array();
	foreach ($progs as $prog) {
		$dir = locateDir($prog);
		if ($dir) {
			$file = "$dir/$prog";
			if (fs_is_executable($file)) {
				fs_exec($file, $results, $status);
				if (isset($count[$status])) {
					$count[$status]++;
				} else {
					$count[$status]=1;
				}
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

function make_separator($key, $arr)  {
		$buf="\n<tr><td class=\"separator\" colspan=\"2\"><b>". $arr["title"] ."</b></td></tr>";
		if( isset($arr["desc"])) {
			$buf .= "\n<tr><td class=\"desc\" colspan=\"2\">". $arr["desc"] ."</td></tr>";
		}
		$buf .= "\n<tr><td>&nbsp;</td></tr>";
		return $buf;
}

function array_stripslashes($subject) {
	if (is_string($subject)) {
		return stripslashes($subject);
	}
	if (!is_array($subject)) {
		return ($subject);
	}
	$ret=array();
	foreach ($subject as $key => $value) {
		$ret[$key]=array_stripslashes($value);
	}
	return $ret;
}
function array_str_replace($search, $replace, $subject) {
	if (is_string($subject)) {
		return str_replace($search, $replace, $subject);
	}
	if (!is_array($subject)) {
		return ($subject);
	}
	$ret=array();
	foreach ($subject as $key => $value) {
		$ret[$key]=array_str_replace($search, $replace, $value);
	}
	return $ret;
}

function newIn($version) {
	$buf = "\n\t<br><font color=blue><b>(";
	$buf .= sprintf(_("this is new in version %s"), $version);
	$buf .= ")</b></font>";
	return $buf;
}
?>
