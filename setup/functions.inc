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
	} else if (isset($arr["type"]) && $arr["type"] == "password") {
		$col2 = form_password($key, $arr);
	} else if (isset($arr["type"]) && $arr["type"] == "nv_pairs") {
		$col2 = form_nv_pairs($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'print_services') {
		$col2 = form_print_services($key, $arr);
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

function form_password($key, $arr) {
	if (isset($arr["attrs"])) {
		$attrs = make_attrs($arr["attrs"]);
	} else {
		$attrs = '';
	}

	if (empty($arr['value'])) {
	    $arr['value'] = array('', '', '', '');
	}
	return "<input type=\"password\" name=${key}[0] value=\"{$arr['value'][0]}\" $attrs> "
		. '<br>'
		. "<input type=\"password\" name=${key}[1] value=\"{$arr['value'][1]}\" $attrs> "
		. _('Please retype your password here')
		. "\n<input type=\"hidden\" name=${key}[2] value=\"{$arr['value'][2]}\">"
		. "\n<input type=\"hidden\" name=${key}[3] value=\"{$arr['value'][3]}\">";
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
		if ($arr["value"] && in_array($item, $arr["value"])) {
			$selected = "CHECKED";
		}
		$buf .= "\n\t<br><input name=\"${key}[]\" value=\"$item\" type=\"checkbox\" $selected>" . $value ;
	}
	$buf .="</td></tr></table>";
	return $buf;
}

function form_print_services($key, $arr) {

	$buf= "\n\t<table border=\"0\">";
	foreach ($arr['services'] as $item => $data) {
	    if (isset($arr['value'][$item])) {
		if (is_array($arr['value'][$item])) {
			$value = $arr['value'][$item];
			if (!isset($value['checked'])) {
				$value['checked'] = false;
			}
		} else {
			$value = array('checked' => true);
		}
	    } else {
		$value = array('checked' => false);
	    }
	    $checked = $value['checked'] ? ' checked' : '';
	    $buf .= "\n\t\t<tr><td valign=\"top\">\n\t\t\t<input name=\"${key}[$item][checked]\" value=\"checked\" type=\"checkbox\"$checked><a href=\"${data['url']}\">${data['name']}</a>";
	    if (isset($data['radio'])) {
		if (!isset($value['donation'])) {
		    $value['donation'] = 'yes';
		}
		$buf .= "\n\t\t\t</td><td valign=\"top\">";
		foreach ($data['radio'] as $radio => $values) {
		    $checked = $value['donation'] === $radio
			? ' checked' : '';
		    $buf .= "\n\t\t\t<input name=\"${key}[$item][donation]\" value=\"$radio\" type=\"radio\"$checked>" . $values;
		}
	    }
	    $buf .= "\n\t\t</td></tr>";
	}
	$buf .="\n\t</table>\n\t";
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
	$warn = array();
	if ($GALLERY_PHP_VALUE_OK) {
		$success[] = _("I can read your <b>.htaccess</b> file.");
	} else {
		$fail["fail-htaccess"] = 1;
	}

	return array($success, $fail, $warn);
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
		$success[] = sprintf(_("PHP v%s is OK."), phpversion());
	}

	return array($success, $fail, $warn);
}
function check_mod_rewrite() 
{
	global $GALLERY_REWRITE_OK;

	$success = array();
	$fail = array();
	$warn = array();
	if ($GALLERY_REWRITE_OK) {
		$success[] = _("<b>mod_rewrite</b> is enabled.");
	} else {
		$fail["fail-mod-rewrite"] = 1;
	}

	return array($success, $fail, $warn);
}

function check_exif($location="")
{
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$bin = fs_executable("jhead");

	if ($location) {
		$d = locateDir($bin, $location);
	} else {
		$d = locateDir($bin, isset($gallery->app->use_exif) ? dirname($gallery->app->use_exif) : "");
	}
	if (!$d) {
		$warn["fail-exif"] = _("Can't find <i>jhead</i>");
	} else {
		$success[] = _("<b>jhead</b> binary located.");
	}

	return array($success, $fail, $warn);
}

function check_graphics($location="", $graphtool = "")
{
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();
	if (!strcmp($graphtool, "ImageMagick")) {
		$success[] = _("NetPBM not being used in this installation.");
	    	return array($success, $fail);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $success[] = _("Cannot verify this path (it's not in your open_basedir list).");
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
		$success[] = sprintf(_("%d of %d NetPBM binaries located."),
				count($netpbm), count($netpbm) );
	}

	return array($success, $fail, $warn);
}

function check_graphics_im($location="", $graphtool="")
{
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	if (!strcmp($graphtool, "NetPBM")) {
		$success[] = "ImageMagick not being used in this installation.";
	    	return array($success, $fail);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $success[] = _("Cannot verify this path (it's not in your open_basedir list).");
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
		$success[] = sprintf(_("%d of %d ImageMagick binaries located."),
				count($imagick), count($imagick));
	}

	return array($success, $fail, $warn);
}

function check_jpegtran($location="")
{
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$bin = fs_executable("jpegtran");

	if ($location) {
		$d = locateDir($bin, $location);
	} else {
		$d = locateDir($bin, isset($gallery->app->use_jpegtran) ? $gallery->app->use_jpegtran : "");
	}
	if (!$d) {
		$warn["fail-jpegtran"] = _("Can't find <i>jpegtran</i>!");
	} else {
		$success[] = _("<b>jpegtran</b> binary located.");
	}

	return array($success, $fail, $warn);
}

function check_gettext()
{
	$fail = array();
	$success = array();
	$warn = array();
	if (gettext_installed()) {
		$success[] = _("PHP has <b>GNU gettext</b> support.");
	} else {
		$warn["fail-gettext"] = _("PHP does not have <b>GNU gettext</b> support.");
	}
	return array($success, $fail, $warn);
}

function check_gallery_languages()
{
	global $GALLERY_BASEDIR;
	global $gallery;
	$fail = array();
	$success = array();
	$warn = array();
	$nls = getNLS();

	$languages=gallery_languages();
	if (sizeof($languages) == 0) {
		$fail["fail-gallery-languages"] = _("No languages found."); // should never occur!
	} else if (sizeof($languages) == 1 ) {
		$warn['only_english'] = _("It seems you didn't download any additional languages. This is not a problem! Gallery will appear just in English. Note: If this is not true, check that all files in locale folder are readable for the webserver, or contact the Gallery Team.");
		$warn['nocolor'] = true;
	}
	else {
	$success[] = sprintf(_("%d languages are available.  If you are missing a language please visit the %sGallery download page%s."),
					sizeof($languages),
					"<a href=\"$gallery->url\">",
					'</a>');
	}
	return array($success, $fail, $warn);
}

function check_gallery_version()
{
	global $GALLERY_BASEDIR;
	global $gallery;
	$fail = array();
	$success = array();
	$warn = array();

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
	return array($success, $fail, $warn);
}

function check_absent_locales()
{
	global $GALLERY_BASEDIR;
	global $locale_check;
	$fail = array();
	$success = array();
	$warn = array();
	$msg = '';

	$available=$locale_check["available_locales"];
	$maybe=$locale_check["maybe_locales"];
	$unavailable=$locale_check["unavailable_locales"];

	if($locale_check != NULL && sizeof($unavailable) ==0) {
		$success[] = _("All gallery locales are available on this host.");
	} else if( (sizeof($maybe) + sizeof($unavailable)) >0) {
		if (sizeof($maybe) >0) {
			$msg = sprintf(_("There are %d locales that Gallery was unable to locate. You may need to select manually date formats. "),sizeof($maybe));
		}

		if (sizeof($unavailable) >0) {
			if(sizeof($maybe) >0) $msg.="<p></p>";

			$msg .= sprintf(_("Dates in %d languages may not be formatted properly, because the corresponding locales are missing. You may need to select manually the date formats for these."),sizeof($unavailable));
		}
		$warn[] = $msg;
	} else {
		if (ini_get('open_basedir') && getOS() != OS_LINUX) {
			$warn[] = sprintf(_("We were unable to detect any locales.  However, your PHP installation is configured with the %s restriction so this may be interfering with the way that we detect locales.  Unfortunately this means the date format will not change for different languages.  However, it is OK to continue."),
				'<b><a href="http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir" target="_blank">open_basedir</a></b>');
		} else {
			if (getOS() == OS_LINUX) {
				$fail[] = sprintf(_("We were unable to detect any system locales. Multi-language functions will be disabled. Please install the corresponding locales or ask your administrator to do this. This problem is known on %s systems. In this case please have a look at this %sDebian locale HowTo%s."),"Debian", '<a href="http://people.debian.org/~schultmc/locales.html" target="_blank">', "</a>");
			} else {
				$warn[] = _("Only the default locale for this machine is available, so date format will not change for different languages.");
			}
		}
	}
	$warn['nocolor'] = true;	
	return array($success, $fail, $warn);
}

function check_locale() {
	$nls=getNLS();
	$gallery_languages=array_keys(gallery_languages());
	$system_locales=array();
	
	$available_locales=array();
	$maybe_locales=array();
	$unavailable_locales=array();

	/* Lets see which system locales are installed. */
	if (getOS() != OS_WINDOWS) { 
		# Unix / Linux
		# Check which locales are installed

		// PHP on NetBSD seems to crash when explode on empty string
		$se_locale=shell_exec("locale -a");
		if ($se_locale != NULL) {
			$tmp=explode("\n", $se_locale);
		} else {
			$tmp=array();
		}
		if(count($tmp) >3) {
			$system_locales=$tmp;
		} elseif (@is_readable("/etc/locale.gen")) {
			$system_locales=explode("\n",shell_exec('grep -v -e "^#" /etc/locale.gen | cut -d " " -f 1'));
		} elseif (@is_readable("/usr/share/locale")) {
			$system_locales=explode("\n",shell_exec("ls /usr/share/locale"));
		} elseif (@is_readable("/usr/local/share/locale")) {
			$system_locales=explode("\n",shell_exec("ls /usr/local/share/locale"));
		}
	}

	/* DAMN, there are none we use Linux and our PHP uses gettext*/ 
	if( sizeof($system_locales) == 0 && getOS() == OS_LINUX && gettext_installed()) {
		return NULL;
	}

	/* There were at least one system locale
	** Now lets test if our languages are supported by the system
	*/
	foreach ($gallery_languages as $locale) {
		$aliases=array();
		
		/* Found an supported one, put it in availables */
		if ( (in_array($locale, $system_locales)) || (setlocale(LC_ALL, $locale))) {
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
				$sub='^(' . substr($locale,0,2) . '|' . implode('|', $keylist) . ')';
                                foreach ($system_locales as $key => $value) {
                                        if (ereg($sub, $value)) {
                                                $aliases[] = $value;
                                        }
                                }
			}
		} else {
                        foreach ($system_locales as $key => $value) {
                                if (ereg('^' . substr($locale,0,2), $value)) {
                                        $aliases[] = $value;
                                }
                        }
		}

		$aliases=array_unique($aliases);
		$noway=Array ('zh_TW.eucTW'); 
		if ($aliases) {
			foreach ($aliases as $test) {
				// We do this because all locales in $noway seem to crash at least some NetBSD
				// Maybe changed in future
				if (!in_array($test,$noway)) {
					if (setlocale(LC_ALL,$test)) {
						$maybe_locales[$locale][]=$test;
					}
				}
			}
			if (! isset($maybe_locales[$locale])) {
                                $unavailable_locales[] = $locale;
                        }
		} else {
			$unavailable_locales[]=$locale;
		}
	}


	// Set locale correct back      
	if (isset($gallery->locale)) {
		setlocale(LC_ALL,$gallery->locale);  
	} else {
		setlocale(LC_ALL,"");
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

	// If we are in Linux, our PHP has gettext, 
	// but we could not find any locale we skip the whole aliasing part.
	if($locales == NULL) return;

	$nls=getNLS();

	$block_start_done=false;
	
	$nr=0;
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
		$nr++;
		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b><br> ".sprintf(_("Locale Selection required")),
					"desc" => _("There is more than one suitable locale installed on your machine for the following languages.  Please chose the one you think is most suitable.") .
					"<p></p>" .
					_("This is <b>only</b> for date & time format. You only need to edit the languages you enabled above")
					);
		}
		$index = $nls['language'][$key] ;
		$choices=array();
		foreach ($aliases as $value) { 
			$choices[$value]=$value;
		}
		$results["locale_alias['$key']"] = array (
			"prompt" => $nr .".) ". $nls['language'][$key],
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"type" => "block_element",
			"choices" => $choices,
			"value" => current($choices),
			"remove_empty" => true
			);

			
        }
	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
	}
	$block_start_done=false;

	$choices=array();
	if (sizeof($available) > 0) {
		if (getOS() == OS_OTHER) $choices=array("" => _("System locale"));
		foreach ($available as $choice => $value) { 
			$choices[$choice]=$nls['language'][$value];
		}

		$avail_keys=array_keys($available);
	} elseif (sizeof($maybe) > 0) {
		if (getOS() == OS_OTHER) $choices=array("" => _("System locale"));
		foreach ($maybe as $key => $aliases) {
			foreach ($aliases as $choice) {
				$choices[$choice]=$choice;
			}
		}

		$avail_keys=array_keys($choices);
	} else {
		if (getOS() == OS_OTHER) {
			$choices=array("" => _("System locale"));
			$array_keys=$choices;
		} else {
			$skip=true;
		}
	}
	
	if (! isset ($skip)) {
	$avail_keys=array_keys($choices);
        foreach ($unavailable as $key) {
		if (sizeof($choices) == 1) {
			$results["locale_alias['$key']"] = array (
				"type" => "hidden", 
				"value" => $avail_keys[0],
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]",
				"allow_empty" => (getOS() == OS_OTHER) ? true : false
				);
			continue;
		}

		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b><br> ".sprintf(_("Locale problems")),
						"desc" => _("There are no apparently suitable locales installed on your machine for the following languages.  Please choose the one you think is most suitable.") .
							"<p></p>" .
							_("This is <b>only</b> for date & time format. You only need to edit the languages you enabled above")
							);
		}
		$index = $nls['language'][$key] ;
		$results["locale_alias['$key']"] = array (
			"prompt" => $nls['language'][$key],
			"name" => "locale_alias",
			"key" => $key,
			"type" => "block_element",
			"choices" => $choices,
			"value" => "",
			"allow_empty" => (getOS() == OS_OTHER) ? true : false
			);
        }
	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
	}
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
	$warn = array();
	$safe_mode = ini_get("safe_mode");
	if (empty($safe_mode) ||
	    !strcasecmp($safe_mode, "off") ||
	    !strcasecmp($safe_mode, "0") ||
	    !strcasecmp($safe_mode, "false")) {
		$success[] = _("<b>safe_mode</b> is off.");
	} else {
		$fail["fail-safe-mode"] = 1;
	}
	return array($success, $fail,$warn);
}

function check_magic_quotes()
{
	$fail = array();
	$success = array();
	$warn = array();
	if (!get_magic_quotes_gpc()) {
		$success[] = _("<b>magic_quotes</b> are off.");
	} else {
		$fail["fail-magic-quotes"] = 1;
	}

	return array($success, $fail, $warn);
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

function array_urldecode($subject) {
	if (is_string($subject)) {
		return urldecode($subject);
	}
	if (!is_array($subject)) {
		return ($subject);
	}
	$ret=array();
	foreach ($subject as $key => $value) {
		$ret[$key]=array_urldecode($value);
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

function verify_password($passwords) {
	$success = array();
	$fail = array();
	if ($passwords[2] === $passwords[3]) {
		$success[] = true;
	} else {
		$fail[] = _('Your admin passwords do not match!');
	}
	return array($success, $fail);
}

function verify_email($emailMaster)
{
	global $adminEmail, $senderEmail, $emailGreeting;
	$fail = array();
	$success = array();
       	if ($emailMaster == "no") {
	       	$success[] = _("OK");
	       	return array($success, $fail);
	}

	if (validate_email($adminEmail)) {
		$success[] = _("Valid admin email address given.");
	} else {
		$adminEmail = ereg_replace('([[:space:]]+)', '', $adminEmail);
		$emails = array_filter1(explode(',', $adminEmail));
		$size  = sizeof($emails);

		if ($size < 1) {
			$fail[]= _("You must specify valid admin email addresses");
		} else {
			$adminEmail="";
			$join="";
		       	foreach ($emails as $email) {
			       	$adminEmail .= "$join$email";
			       	$join=",";
			       	if (!validate_email($email)) {
				       	$fail[] = sprintf(_("%s is not a valid email address."), 
							$email);
			       	} else {
				       	$success[] = "Valid admin email given.";
			       	}
		       	}
	       	}
	}
       	if (validate_email($senderEmail)) {
	       	$success[] = _("Valid sender email address given.");
       	} else {
	       	$fail[]= _("You must specify a valid sender email address");
       	}
       	if (!empty($emailGreeting) && !strstr($emailGreeting, "!!USERNAME!!")) {
	       	$fail[]= sprintf(_("You must include %s in your welcome email"), "<b>!!USERNAME!!</b>");
       	}
       	if (!empty($emailGreeting) && 
			!strstr($emailGreeting, "!!PASSWORD!!" ) && 
			!strstr($emailGreeting, "!!NEWPASSWORDLINK!!" )) {
	       	$fail[]= sprintf(_("You must include %s or %s in your welcome email"), 
				"<b>!!PASSWORD!!</b>",
				"<b>!!NEWPASSWORDLINK!!</b>");
       	}
       	return array($success, $fail);
}

function check_ecards($num)
{
    if ($num < 15 || $num > 365) {
		$fail = array();
		$fail["fail-ecardPrune"]++;
	} else {
		$success = array();
		$success[] = "Valid value specified.";
    }
	return array($success, $fail);
}

function newIn($version) {
	$buf = "\n\t<br><font color=blue><b>(";
	$buf .= sprintf(_("this is new in version %s"), $version);
	$buf .= ")</b></font>";
	return $buf;
}
function returnToConfig() {
	$buf = sprintf(_("Return to %s."), '<a href="index.php">' .
			_("Configuration Wizard") . '</a>');
	return $buf;
}
if (!function_exists('array_filter1')) {
       	function array_filter1($input, $function=NULL) {
		$output=array();
	       	foreach ($input as $name => $value) {
		       	if ($function && $function($value)) {
			       	$output[$name]=$value;
		       	} else if ($value) {
			       	$output[$name]=$value;
		       	}
		}
	       	return $output;
       	}
}

function check_admins() {

	global $gallery;
	global $GALLERY_BASEDIR;

	$admins=array();
	
	if (isset($gallery->app->userDir)) {
		require($GALLERY_BASEDIR . "classes/User.php");
		require($GALLERY_BASEDIR . "classes/EverybodyUser.php");
		require($GALLERY_BASEDIR . "classes/NobodyUser.php");
		require($GALLERY_BASEDIR . "classes/LoggedInUser.php");
		require($GALLERY_BASEDIR . "classes/UserDB.php");

		require($GALLERY_BASEDIR . "classes/gallery/UserDB.php");
        	require($GALLERY_BASEDIR . "classes/gallery/User.php");

	
		$userDB = new Gallery_UserDB();

		$admins=array();
		foreach ($userDB->getUidList() as $uid) {
			$tmpUser = $userDB->getUserByUid($uid, true);

			if ($tmpUser->isAdmin()) {
				$admins[]=$tmpUser->getUsername();
			}
		}
	}

	if (empty($admins)) {
		$result=array(
			'desc' => sprintf(_('You must enter a password for the %s account.'), '<b>admin</b>')
		);
	}
	else if (! in_array("admin",$admins)) {
		$result=array(
			"desc" => sprintf(_("It seems you've already configured Gallery, because there are %d admin accounts, but not a user called %s.  You don't have to enter a password.  But if you do, Gallery will create an administrator account called %s with that password."), sizeof($admins), '<b>admin</b>', '<b>admin</b>') . newIn('1.4.1'),
			"optional" => 1,
			"remove_empty" => true
		);
	}
	else {
		$result=array(
			"desc" => sprintf(_("It seems you've already configured Gallery, because the %s user exists.  You don't have to enter a password.  But if you do, Gallery will change the password for the %s user."), '<b>admin</b>', '<b>admin</b>') . newIn("1.4.1"),
			"optional" => 1,
			"remove_empty" => true
		);
	}

$result=array_merge($result,array(
	"prompt" => _("Admin password"),
	"type" => "password",
	"dont-write" => 1,
	'verify-func' => 'verify_password',
	"value" => "",
	"attrs" => array("size" => 20)
));
return $result;
}
?>
