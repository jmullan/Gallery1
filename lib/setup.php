<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

function evenOdd_row($fields) {
	$buf = '';

	/* why was this added ? Jens Tkotz, 05.04.2005 */
	// $f0 = str_replace(" ", "&nbsp;", $fields[0]);
	$f0 = $fields[0];
	if (isset($fields[4])) {
                        $f0 .= '&nbsp;<span class="littlered">*</span>';
	}
	if ($fields[3] == "block_element") {
		$buf .= "\n<tr><td class=\"shortdesc\" width=\"30%\">$f0</td><td class=\"shortdesc\">".$fields[1]."</td></tr>";
	} else if ($fields[3] == "block_start") {
		$buf .= "\n<tr><td class=\"shortdesc\" colspan=\"2\" valign=\"top\">";
		$buf .= "\n<table><tr>";
		$buf .= "\n\t<td class=\"content\" valign=\"top\">$f0<p>";
		$buf .= "\n\t".$fields[2]."</td>";
		$buf .= "\n</tr></table >";
	} else if ($fields[3] == "block_end") {
		$buf .= "\n</td></tr>";
	} else {
		$buf .= "\n<tr>";
		$buf .= "\n\t<td class=\"shortdesc\" width=\"40%\" valign=\"top\">$fields[0]</td>";
		$buf .= "\n\t<td class=\"shortdesc\" valign=\"top\">$fields[1]</td>";
		$buf .= "\n</tr>";
		$buf .= "\n<tr>";
		if (!empty($fields[2])) {
			$buf .= "\n\t<td class=\"desc\" colspan=\"2\" valign=\"top\">$fields[2]</td>";
			$buf .= "\n</tr>\n";
		}
	}

	return $buf;
}

function make_attrs($attrList) {
	$attrs = '';
	if ($attrList) {
		/*
		** I commented this out, because it produces non valid html for textareas.
		** 06.04.2004, Jens Tkotz
		if (!isset($attrList["size"])) {
			$attrList["size"] = 40;
		}
		*/

		foreach ($attrList as $attrKey => $attrVal) {
			$attrs .= "$attrKey=\"$attrVal\" ";
		}
	}
	return $attrs;
}

function make_fields($key, $arr) {
	if (isset($arr['prompt'])) {
		$col1 = $arr['prompt'];
	} else {
		$col1 = '';
	}
	if (isset($arr['type']) && 
		($arr['type'] == 'text' || $arr['type'] == 'hidden' || $arr['type'] == 'checkbox')) {
		$col2 = form_input($key, $arr);
	} else if (isset($arr['choices'])) {
		$col2 = form_choice($key, $arr);
	} else if (isset($arr['multiple_choices'])) {
		$col2 = form_multiple_choice($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'textarea') {
		$col2 = form_textarea($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'table_values') {
		$col2 = form_table_values($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'colorpicker') {
		$arr['name'] = $key;
		$col2 = showColorpicker($arr);
	} else if (isset($arr['type']) && $arr['type'] == 'password') {
		$col2 = form_password($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'nv_pairs') {
		$col2 = form_nv_pairs($key, $arr);
	} else if (isset($arr['type']) && $arr['type'] == 'print_services') {
		$col2 = form_print_services($key, $arr);
	} else {
		$col2 ='';
	}
	if (isset($arr['desc'])) {
		$col3 = $arr['desc'];
	} else {
		$col3 = '';
	}
	$col4 = isset($arr['type']) ?  $arr['type'] : NULL;
	$col5 = isset($arr['required']) ? true : NULL;

	return array($col1, $col2, $col3,$col4,$col5);
}

function form_textarea($key, $arr) {
	$attrs = make_attrs($arr["attrs"]);
	return "<textarea name=\"$key\" $attrs>$arr[value]</textarea>";
}

function form_input($key, $arr) {
    $type  = (isset($arr['type'])) ? 'type="'.$arr['type'].'"' : '';

    $attrs = (isset($arr['attrs'])) ? make_attrs($arr['attrs']) : '';

    $name  = (isset($arr['name'])) ? $arr['name'] : $key;
	
    return "<input $type name=\"$name\" value=\"$arr[value]\" $attrs>";
}

function form_password($key, $arr) {
	if (isset($arr["attrs"])) {
		$attrs = make_attrs($arr["attrs"]);
	} else {
		$attrs = '';
	}

	if (empty($arr['value'])) {
	    $arr['value'] = array('', '', '', '');
	} elseif (!is_array($arr['value'])) {
	    $arr['value'] = array($arr['value'], $arr['value'], $arr['value'], $arr['value']);
        }
	return "<input type=\"password\" name=\"${key}[0]\" value=\"{$arr['value'][0]}\" $attrs> "
		. '<br>'
		. "<input type=\"password\" name=\"${key}[1]\" value=\"{$arr['value'][1]}\" $attrs> "
		. _('Please retype your password here')
		. "\n<input type=\"hidden\" name=\"${key}[2]\" value=\"{$arr['value'][2]}\">"
		. "\n<input type=\"hidden\" name=\"${key}[3]\" value=\"{$arr['value'][3]}\">";
}

function form_nv_pairs($key, $arr) {
	if (isset($arr["attrs"])) {
		$attrs = make_attrs($arr["attrs"]);
	} else {
		$attrs = '';
	}
	$x=0;
	$buf="\n<table>"
		. "<tr>"
		. "<td><b>". _("Name") . "</b></td>"
		. "<td><b>". _("Value") ."</b></td>"
		. "</tr>";
	foreach ($arr["value"] as $result) {
		$name=$result["name"];
		$value=$result["value"];
		$buf .= '<tr>'
			. '<td><input type="text" name="' . $key ."[$x][name] \" value=\"$name\" $attrs></td>\n";
		$buf .= '<td><input type="text" name="' . $key  ."[$x][value]\" value=\"$value\" $attrs></td>"
			. "</tr>\n";
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
	if (empty($arr["multiple_choices"])) {
	   return _("No content");
	}

	$buf= '<table><tr><td valign="top">';
	$count=0;
	$column=0;
	foreach ($arr["multiple_choices"] as $item => $value) {
		if ($item == 'addon') continue;
		if ($count%15 ==0) {
			$buf .= "</td>\n<td valign=\"top\">";
		}
		$count++;
		$column++;
		$selected = "";
		if (is_array($arr["value"]) && in_array($item, $arr["value"])) {
			$selected = "CHECKED";
		}
		$buf .= "\n\t<br><input name=\"${key}[]\" value=\"$item\" type=\"checkbox\" $selected>" . $value ;
	}
	$buf .="</td></tr>";
	if (isset($arr['multiple_choices']['addon'])) {
		$buf .="\n<tr><td colspan=$column>++". $arr['multiple_choices']['addon'] . "\n++</td></tr>";
	}
	$buf .="</table>";
	return $buf;
}

function makeMultipleChoiceContent($array) {
    $multipleChoiceContent = array();

    foreach($array as $key => $content) {
	$multipleChoiceContent[$key] = '<a href="'. $content['url'] .'">'. $content['name'] .'</a>';
	if (isset($content['description'])) {
	    $multipleChoiceContent[$key] .= ' '. $content['description'];
	}
    }

    return $multipleChoiceContent;
}

/* in progress */
function form_table_values($key, $arr) {
    if (empty($arr['elements'])) {
	return _("No content");
    }

    $jTable = new galleryTable();

    $jTable->setHeaders($arr['columns']);
    $jTable->setColumnCount(sizeof($arr['columns']));

    foreach($arr['elements'] as $element) {
	$jTable->addElement(array('content' => $element));
    }

    return $jTable->render();
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
            if (!empty($data['desc'])) {
		$buf .= ' - ' . $data['desc'];
	    }
	    $buf .= "\n\t\t</td></tr>";
	}
	$buf .="\n\t</table>\n\t";
	return $buf;
}

function getPath() {

    /* Start with the server user's path */
    if (getOS() != OS_WINDOWS) {
    	$path = explode(":", getenv('PATH'));
    }
    else {
    	$path = explode(';', getenv('PATH'));
    }

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
    $path[] = GALLERY_BASE . "/netpbm";
    $path[] = GALLERY_BASE . "/bin";
    $path[] = GALLERY_BASE . "/bin/netpbm";
    $path[] = GALLERY_BASE . "/bin/jhead";

    return $path;
}

function locateDir($filename, $extraDir = '', $ignorePath = false) {
	if (fs_file_exists("$extraDir/$filename")) {
		return $extraDir;
	}

	if ($ignorePath) {
		return '';
	}

	foreach (getPath() as $path) {
		if (fs_file_exists("$path/$filename") && !empty($path)) {
			return $path;
		}
	}
}

function locateFile($filename) {
	foreach (getPath() as $path) {
		if (fs_file_exists("$path/$filename") && !empty($path)) {
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
	return gallery_error("// " . _("Missing value") . ": <b>$desc</b>!");
}

function error_row_wrap($buf) {
	return "// $buf";
}

function check_exec() {
	$disabled = "" . ini_get("disable_functions");

	$success = array();
	$fail = array();
	$warn = array();
	if (!empty($disabled)) {
		foreach(explode(',', $disabled) as $disabled_func) {
			if(eregi('^exec$', $disabled_func)) {
				$fail["fail-exec"] = 1;
			}
		}
	}

	if (empty($fail['fail-exec'])) {
		$success[] = _("<b>exec()</b> is not disabled on this server.");
	}

	return array($success, $fail, $warn);
}

function check_htaccess() {
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

function check_php() {
	global $MIN_PHP_MAJOR_VERSION;

	$version = phpversion();
	$success = array();
	$fail = array();
	$warn = array();

	if (!function_exists('version_compare') || !version_compare($version, "4.1.0", ">=")) {
		$fail['fail-too-old'] = 1;
	} else {
		$success[] = sprintf(_("PHP v%s is OK."), $version);
	}

	return array($success, $fail, $warn);
}
function check_mod_rewrite()  {
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

function check_exif($location = '') {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$bin = fs_executable('jhead');

	if ($location) {
		$dir = locateDir($bin, $location);
	} else {
		$dir = locateDir($bin, isset($gallery->app->use_exif) ? dirname($gallery->app->use_exif) : "");
	}
	if (empty($dir)) {
		$warn["fail-exif"] = _("Can't find <i>jhead</i>");
	} else {
		$success[] = _("<b>jhead</b> binary located.");
	}

	return array($success, $fail, $warn);
}

function check_graphics($location = '', $graphtool = '') {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();
	
	$missing_critical = array();
	$missing = 0;
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
		fs_executable("pnmflip"),
		fs_executable("pnmcomp"),
	);

	$fallback = array(
		fs_executable("pnmtojpeg") => fs_executable("ppmtojpeg"),
		fs_executable("pnmcomp")   => fs_executable("pamcomp")
	);

	$optional = array(
		fs_executable("pnmcomp") => 
			_("Without pnmcomp and pamcomp gallery will not be able to watermark images, unless you use ImageMagick and have the composite binary installed."),
	);
	
	$missing_optional = 0;
	
	/* Start checks */
	
	if ($graphtool == 'ImageMagick') {
		$success[] = _("NetPBM not being used in this installation.");
		return array($success, $fail, $warn);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $warn[] = _("Cannot verify this path (it's not in your open_basedir list).");
	    return array($success, $fail, $warn);
	}

	foreach ($netpbm as $bin) {
		if (!empty($location)) {
			$dir = locateDir($bin, $location, true);
		}
		elseif (isset($gallery->app->pnmDir)) {
			$dir = locateDir($bin, $gallery->app->pnmDir, true);
		}
		else {
			$dir = locateDir($bin);
		}

		/* If we can't find the primary file, look for the fallback file instead. */
		if (empty($dir) && isset($fallback[$bin])) {
			$newbin = $fallback[$bin];
			if (!empty($location)) {
				$dir = locateDir($newbin, $location, true);
			}
			elseif (isset($gallery->app->pnmDir)) {
				$dir = locateDir($newbin, $gallery->app->pnmDir, true);
			}
			else {
				$dir = locateDir($newbin);
			}
			if (!empty($dir)) {
				$bin = $newbin;
			}
		}
		
		if (empty($dir)) {
			if (isset($optional[$bin])) {
				$warn[$bin] = sprintf(_("Missing optional binary %s. %s"), $bin, $optional[$bin]);
			}
			else {
				$missing_critical[$bin] = sprintf(_("Can't find %s!"), "<i>$bin</i>");
			}
			$missing++;
		}

		if (!empty($dir) && inOpenBasedir($dir)) {
		    if (!fs_is_executable("$dir/$bin")) {
				$warn[$bin] = sprintf(_("%s is not executable!"),
					"<i>$bin</i> "); 
			}
		}
	}
	
	if ($missing == count($netpbm)) {
		$fail["fail-netpbm"] = 1;
		/* Any other warning doesnt care */
		$warn = array();
	}
	elseif ($missing > 0) {
		$warn[] = sprintf(_("%d of %d NetPBM binaries located."), 
			count($netpbm) - $missing, count($netpbm));
		
		if(count($missing_critical) > 0) {
			$fail["fail-netpbm-partial"] = array_values($missing_critical);
		}
	} else {
		$success[] = sprintf(_("%d of %d NetPBM binaries located."),
			count($netpbm), count($netpbm));
	}

	return array($success, $fail, $warn);
}

function check_graphics_im($location = '', $graphtool = '') {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$missing_critical = array();
	$missing = 0;
	$imagick = array(
		fs_executable("identify"), 
		fs_executable("convert"),
		fs_executable("composite"),
	);

	$optional = array(
		fs_executable("composite") => 
			_("Without composite gallery will not be able to watermark images, except you use NetPBM and have the pnmcomp binary installed."),
	);


	/* Begin Checks */
	if ($graphtool == 'NetPBM') {
		$success[] = _("ImageMagick not being used in this installation.");
	    	return array($success, $fail, $warn);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
	    $success[] = _("Cannot verify this path (it's not in your open_basedir list).");
	    return array($success, $fail);
	}

	foreach ($imagick as $bin) {
		if (!empty($location)) {
			$dir = locateDir($bin, $location, true);
		}
		elseif (isset($gallery->app->ImPath)) {
			$dir = locateDir($bin, $gallery->app->ImPath, true);
		}
		else {
			$dir = locateDir($bin);
		}

		if (empty($dir)) {
			if (isset($optional[$bin])) {
				$warn[$bin] = sprintf(_("Missing optional binary %s. %s"), $bin, $optional[$bin]);
			}
			else {
				$missing_critical[$bin] = sprintf(_("Can't find %s!"), "<i>$bin</i>");
			}
			$missing++;
		}

		if (!empty($dir) && inOpenBasedir($dir)) {
		    if (!fs_is_executable("$dir/$bin")) {
				$warn[$bin] = sprintf(_("%s is not executable!"),
					"<i>$bin</i> "); 
			}
		}
	}
	
	if ($missing == count($imagick)) {
		$fail["fail-imagemagick"] = 1;
		/* Any other warning doesnt care */
		$warn = array();
	}
	elseif ($missing > 0) {
		$warn[] = sprintf(_("%d of %d ImageMagick binaries located."), 
			count($imagick) - $missing, count($imagick));
		
		if(count($missing_critical) > 0) {
			$fail["fail-imagemagick-partial"] = array_values($missing_critical);
		}
	} else {
		$success[] = sprintf(_("%d of %d ImageMagick binaries located."),
			count($imagick), count($imagick));
	}

	return array($success, $fail, $warn);
}

function check_jpegtran($location = '') {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$bin = fs_executable("jpegtran");

	if ($location) {
		$dir = locateDir($bin, $location);
	} else {
		$dir = locateDir($bin, isset($gallery->app->use_jpegtran) ? dirname($gallery->app->use_jpegtran) : "");
	}
	if (!$dir) {
		$warn["fail-jpegtran"] = _("Can't find <i>jpegtran</i>!");
	} else {
		$success[] = _("<b>jpegtran</b> binary located.");
	}

	return array($success, $fail, $warn);
}

function check_gettext() {
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

function check_gallery_languages() {
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
	}
	else {
	$success[] = sprintf(_("%d languages are available.  If you are missing a language please visit the %sGallery download page%s."),
					sizeof($languages),
					"<a href=\"$gallery->url\">",
					'</a>');
	}
	return array($success, $fail, $warn);
}

function check_gallery_version() {
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

function check_absent_locales() {
	global $locale_check;
	$fail = array();
	$success = array();
	$warn = array();
	$msg = '';

	$available = $locale_check["available_locales"];
	$maybe = $locale_check["maybe_locales"];
	$unavailable = $locale_check["unavailable_locales"];

	if($locale_check != NULL && sizeof($unavailable) ==0) {
		$success[] = _("All gallery locales are available on this host.");
	} else if( (sizeof($maybe) + sizeof($unavailable)) > 0) {
		if (sizeof($maybe) > 0) {
			$msg = sprintf(_("There are %d locales that Gallery was unable to locate. You may need to select manually date formats. "),sizeof($maybe));
		}

		if (sizeof($unavailable) > 0) {
			if(sizeof($maybe) > 0) $msg .= "<p></p>";

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
	return array($success, $fail, $warn);
}

function check_locale() {
	$nls = getNLS();
	$gallery_languages = array_keys(gallery_languages());
	$system_locales = array();
	
	$available_locales = array();
	$maybe_locales = array();
	$unavailable_locales = array();

	/* Lets see which system locales are installed. */
	if (getOS() != OS_WINDOWS) { 
		# Unix / Linux
		# Check which locales are installed

		exec("locale -a", $results, $status);

		if(count($results) >2) {
			$system_locales = $results;
		} elseif (@is_readable("/etc/locale.gen")) {
			exec('grep -v -e "^#" /etc/locale.gen | cut -d " " -f 1', $system_locales);
		} elseif (@is_readable("/usr/share/locale")) {
			exec("ls /usr/share/locale", $system_locales);
		} elseif (@is_readable("/usr/local/share/locale")) {
			exec("ls /usr/local/share/locale", $system_locales);
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
		
		/* 
		** First, we try using the full lang, (first 5 chars) if 
		** that doesn't match then 
		** we use the first 2 letter to build an alias list
		**  e.g. nl to find nl_BE or nl_NL
		*/
		if (in_array($locale,$nls['alias'])) {
			$keylist = array_keys($nls['alias'],$locale);
			$aliases = $keylist;
			if (getOS() != OS_WINDOWS) {
				$sub='^(' . implode('|', $keylist) . '|' . substr($locale,0,5) . ')';
                                foreach ($system_locales as $key => $value) {
                                        if (ereg($sub, $value)) {
                                                $aliases[] = $value;
                                        }
					elseif (ereg('^' . substr($locale,0,2),$value)) {
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

	/* DAMN, there are no suitable locales, we use Linux and our PHP uses gettext*/
        if( sizeof($available_locales) == 0 && sizeof($maybe_locales) == 0 && getOS() == OS_LINUX && gettext_installed()) {
                return NULL;
        }

	return array(
		"available_locales" => $available_locales,
		"maybe_locales" => $maybe_locales,
		"unavailable_locales" => $unavailable_locales
	);
}


function config_maybe_locales() {
	global $locale_check, $locales;

	$results = array();
	$locales = $locale_check;
	$available = $locales["available_locales"];
	$maybe = $locales["maybe_locales"];
	$unavailable = $locales["unavailable_locales"];

	// If we are in Linux, our PHP has gettext, 
	// but we could not find any locale we skip the whole aliasing part.
	if($locales == NULL) return $results; // blank array

	$nls = getNLS();

	$block_start_done = false;
	
	$nr=0;
	foreach ($maybe as $key => $aliases) {
		if (sizeof($aliases) < 1) {
			$unavailable[]=$key;
			continue;
		}
/*
		if (sizeof($aliases) == 1) {
			$results["locale_alias['$key']"] = array (
				"type" => "hidden", 
				"value" => array_pop($aliases),
				"desc" => "locale_alias[$key]",
				"prompt" => "locale_alias[$key]"
				);
			continue;
		}
*/
		$nr++;
		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b><br> ".sprintf(_("<b>System</b> locale selection required")),
					"desc" => _("There is more than one suitable <b>system</b> locale installed on your machine for the following languages.  Please chose the one you think is most suitable.") .
					"<p></p>" .
					_("This is <b>only</b> for date & time format. You only need to edit the languages you enabled above")
					);
		}
		$index = $nls['language'][$key] ;

		$choices=array();

		foreach ($aliases as $value) { 
			$choices[$value]=$value;
		}
		if (getOS() != OS_WINDOWS) {
			$choices[""] = _("System locale");
			next($choices);
		}
		$results["locale_alias['$key']"] = array (
			"prompt" => $nr .".) ". $nls['language'][$key],
			"optional" => 1,
			"name" => "locale_alias",
			"key" => $key,
			"type" => "block_element",
			"choices" => $choices,
			"value" => (getOS() != OS_WINDOWS) ? key($choices) : "",
			"allow_empty" => true,
			"remove_empty" => true
			);

			
        }

	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
	}
	$block_start_done=false;

	$choices=array();
	if (getOS() != OS_WINDOWS) $choices=array("" => _("System locale"));
	if (sizeof($available) > 0) {
		foreach ($available as $choice => $value) { 
			$choices[$choice]=$nls['language'][$value];
		}

		$avail_keys=array_keys($available);
	} elseif (sizeof($maybe) > 0) {
		foreach ($maybe as $key => $aliases) {
			foreach ($aliases as $choice) {
				$choices[$choice]=$choice;
			}
		}

		$avail_keys=array_keys($choices);
	} else {
		if (getOS() == OS_OTHER) {
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
				"allow_empty" => true,
				"remove_empty" => true
				);
			continue;
		}

		if (!$block_start_done) {
			$block_start_done=true;
			$results[] = array (
					"type" => "block_start", 
					"prompt" => "<b>(" . _("Advanced") . ")</b><br> ".sprintf(_("<b>System</b> locale problems")),
						"desc" => _("There are no apparently suitable <b>system</b> locales installed on your machine for the following languages.  Please choose the one you think is most suitable.") .
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
			"allow_empty" => true,
			"remove_empty" => true
			);
        }
	if ($block_start_done) {
		$results[] = array ("type" => "block_end");
	}
	}
	return $results;
}

function default_graphics() {
	list ($imageMagick,) = check_graphics_im();
        
	if (count ($imageMagick)) {
		return "ImageMagick";
	} else {
		return "NetPBM";
	}
}

function check_safe_mode() {
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

function check_magic_quotes() {
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

function check_register_globals() {
	$fail = array();
	$success = array();
	$warn = array();

	$globals_enabled = ini_get('register_globals');

	if (!empty($globals_enabled) && !eregi('no|off|false', $globals_enabled)) {
		$fail['warn-register_globals'] = 1;
	}
	else {
		$success[] = _("<b>register_globals</b> is off.");
	}

	return array($success, $fail, $warn);
}

function detect_exec_status() {
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
    $buf ="\n<div class=\"inner\">";
    $buf .= "\n\t<div class=\"separator\">". $arr["title"] ."</div>";
    if( isset($arr["desc"])) {
	$buf .= "\n<div class=\"desc\">". $arr["desc"] ."</div>";
    }
    $buf .="\n</div>";

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

/*
** Check if Magic Quotes are On
** If yes stripslashes and return the cleaned input.
** 
** Jens Tkotz, 02/2004
*/
function stripWQuotesON($mixed) {
        if (get_magic_quotes_gpc()) {
                return array_stripslashes($mixed);
        }
        else {
                return $mixed;
        }

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
		$fail[] = _('Your passwords do not match!');
	}
	return array($success, $fail);
}

function verify_email($emailMaster) {
	global $gallery;

	$fail = array();
	$success = array();
       	if ($emailMaster == "no") {
	       	$success[] = _("OK");
	       	return array($success, $fail);
	}

	if (check_email($gallery->session->configForm->adminEmail)) {
		$success[] = _("Valid admin email address given.");
	} else {
		$adminEmail = ereg_replace('([[:space:]]+)', '', $gallery->session->configForm->adminEmail);
		$emails = array_filter1(explode(',', $gallery->session->configForm->adminEmail));
		$size  = sizeof($emails);

		if ($size < 1) {
			$fail[]= _("You must specify valid admin email addresses");
		} else {
			$adminEmail="";
			$join="";
		       	foreach ($emails as $email) {
			       	$adminEmail .= "$join$email";
			       	$join=",";
				if (! check_email($email)) {
				       	$fail[] = sprintf(_("%s is not a valid email address."), 
							$email);
			       	} else {
				       	$success[] = "Valid admin email given.";
			       	}
		       	}
	       	}
	}
	if (check_email($gallery->session->configForm->senderEmail)) {
	       	$success[] = _("Valid sender email address given.");
       	} else {
	       	$fail[]= _("You must specify a valid sender email address");
       	}
	if (!empty($gallery->session->configForm->emailGreeting) && !strstr($gallery->session->configForm->emailGreeting, "!!USERNAME!!")) {
	       	$fail[]= sprintf(_("You must include %s in your welcome email"), "<b>!!USERNAME!!</b>");
       	}
       	if (!empty($emailGreeting) && 
			!strstr($gallery->session->configForm->emailGreeting, "!!PASSWORD!!" ) &&
			!strstr($gallery->session->configForm->emailGreeting, "!!NEWPASSWORDLINK!!" )) {
	       	$fail[]= sprintf(_("You must include %s or %s in your welcome email"), 
				"<b>!!PASSWORD!!</b>",
				"<b>!!NEWPASSWORDLINK!!</b>");
       	}
       	return array($success, $fail);
}

function check_ecards($num) {
    if ($num < 15 || $num > 365) {
		$fail = array();
		$fail["fail-ecardPrune"]++;
	} else {
		$success = array();
		$success[] = "Valid value specified.";
    }
	return array($success, $fail);
}

function check_gallery_versions()  {
	$fail = array();
	$success = array();
	$warn = array();
       	list($oks, $errors, $warnings) = checkVersions(false);
	if ($errors)  {
		$fail[]=sprintf(_("The following files are out of date, corrupted or missing:<br>&nbsp;&nbsp;&nbsp;&nbsp;%s."), 
				implode('<br>&nbsp;&nbsp;&nbsp;&nbsp;', array_keys($errors))). "<p>" .
			"<br>" . _("This should be fixed before proceeding") . 
		      	"<br>" . sprintf(_("Look at %sCheck Versions%s for more details."), 
					"<a href=check_versions.php>", "</a>");
	} else if ($warnings) {
		$warn[]=sprintf(_("%d files are more recent than expected.  This is OK if you are using pre-release, beta, CVS or modified code."), count($warnings)) .
		      	"<br>" . sprintf(_("Look at %sCheck Versions%s for more details."), 
					"<a href=check_versions.php>", "</a>");
	} else {
		if (count($oks) == 0) {
			$success[] = sprintf(_("All tested files up-to-date."));
		} else {
			$success[]=sprintf(_("All %d tested files up-to-date."), count($oks));
		}
	}
	return array($success, $fail, $warn);
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

	$admins=array();
	
	if (isset($gallery->app->userDir) && fs_is_dir($gallery->app->userDir)) {
		require_once(GALLERY_BASE . '/classes/User.php');
		require_once(GALLERY_BASE . '/classes/EverybodyUser.php');
		require_once(GALLERY_BASE . '/classes/NobodyUser.php');
		require_once(GALLERY_BASE . '/classes/LoggedInUser.php');
		require_once(GALLERY_BASE . '/classes/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/User.php');

	
		$userDB = new Gallery_UserDB();

		$admins=array();
	       	if (isset($userDB)) {
		       	foreach ($userDB->getUidList() as $uid) {
			       	$tmpUser = $userDB->getUserByUid($uid, true);

				if ($tmpUser->isAdmin()) {
				       	$admins[]=$tmpUser->getUsername();
			       	}
		       	}
	       	}
	}

	if (empty($admins)) {
		$result=array(
			'desc' => sprintf(_('You must enter a password for the %s account.'), '<b>admin</b>')
		);
	}
	else if (! in_array("admin",$admins)) {
		if (sizeof($admins) == 1) {
			$desc_text=sprintf(_("It seems you've already configured Gallery, because there is one admin account, but its not called %s."), '<b>admin</b>');
		} 
		else {
			$desc_text=sprintf(_("It seems you've already configured Gallery, because there are %d admin accounts, but no user called %s."), sizeof($admins), '<b>admin</b>');
		}
		$desc_text .= "  " . sprintf (_("You don't have to enter a password.  But if you do, Gallery will create an administrator account called %s with that password."), '<b>admin</b>');
		$result=array(
			"desc" => $desc_text,
			"optional" => 1,
			"remove_empty" => true
		);
	}
	else {
		$result=array(
			"desc" => sprintf(_("It seems you've already configured Gallery, because the %s user exists.  You don't have to enter a password.  But if you do, Gallery will change the password for the %s user."), '<b>admin</b>', '<b>admin</b>'),
			"optional" => 1,
			"remove_empty" => true
		);
	}

	$result = array_merge($result,array(
		"prompt" => _("Admin password"),
		"type" => "password",
		"dont-write" => 1,
		'verify-func' => 'verify_password',
		"value" => "",
		"attrs" => array("size" => 20),
		"required" => true,
	));
	
	return $result;
}

function displayNameOptions() {
	return array (
		"!!FULLNAME!! (!!USERNAME!!)" =>
			sprintf("%s (%s)", _("Full Name"), _("Username")),
		"!!USERNAME!! (!!FULLNAME!!)" =>
			sprintf("%s (%s)", _("Username"), _("Full Name")),
		"!!FULLNAME!!" =>
			_("Full Name"),
		"!!USERNAME!!" =>
			_("Username"),
		"!!MAILTO_FULLNAME!!" =>
			_("Full name that you can click on to send email (mailto:)"),
		"!!MAILTO_USERNAME!!" =>
			_("Username that you can click on to send email (mailto:)"),
		"!!FULLNAME!! (!!EMAIL!!)" =>
			sprintf("%s (%s)", _("Full Name"), _("email address")),
		"!!USERNAME!! (!!EMAIL!!)" =>
			sprintf("%s (%s)", _("Username"), _("email address")),
		     );
}

function check_filedirective() {
	$success = array();
	$fail = array();
	$warn = array();

	if (strstr(__FILE__, 'lib/setup.php') ||
		strstr(__FILE__, 'lib\\setup.php')) {
		$success[]=_("Your version of PHP handles this issue properly.");
	} else {
		$fail['buggy__FILE__'] = 1;
	}

	return array($success, $fail, $warn);
}

function checkVersions($verbose=false) {
	global $gallery;
	/* we assume setup/init.php was loaded ! */

	$manifest=GALLERY_BASE . '/manifest.inc';
	$success=array();
	$fail=array();
	$warn=array();
	if (!fs_file_exists($manifest)) {
	       	$fail["manifest.inc"]=_("File missing or unreadable.  Please install then re-run this test.");
		return array($success, $fail, $warn);
	}
	if (!function_exists('getCVSVersion')) {
		$fail['util.php']=sprintf(_("Please ensure that %s is the latest version."), "util.php");
		return array($success, $fail, $warn);
	}
	include (GALLERY_BASE . '/manifest.inc');
       	if ($verbose) {
	       	print sprintf(_("Testing status of %d files."), count($versions));
	}
	foreach ($versions as $file => $version) {
		$found_version=getCVSVersion($file);
		if ($found_version === NULL) {
		       	if ($verbose) {
			       	print "<br>\n";
			       	print sprintf(_("Cannot read file %s."), $file);
			}
			$fail[$file]=_("File missing or unreadable.");
			continue;
		} else if ($found_version === "") {
			if ($verbose) {
			       	print "<br>\n";
			       	print sprintf(_("Version information not found in %s.  File must be old version or corrupted."), $file);
		       	}
		       	$fail[$file]=_("Missing version");
		       	continue;
	       	} 
		$compare=compareVersions($version, $found_version);
		if ($compare < 0) {
			if ($verbose) {
			       	print "<br>\n";
			       	print sprintf(_("Problem with %s.  Expected version %s (or greater) but found %s."), $file, $version, $found_version);
		       	}
		       	$fail[$file]=sprintf(_("Expected version %s (or greater) but found %s."), $version, $found_version);
	       	} else if ($compare > 0) {
			if ($verbose) {
			       	print "<br>\n";
				print sprintf(_("%s OK.  Actual version (%s) more recent than expected version (%s)"), $file, $found_version, $version);
			}
			$warn[$file]=sprintf(_("Expected version %s but found %s."), $version, $found_version);
		} else {
			if ($verbose) {
			       	print "<br>\n";
			       	print sprintf(_("%s OK"), $file);
		       	}
			$success[$file]=sprintf(_("Found expected version %s."), $version);
		}
			
	}
       	return array($success, $fail, $warn);
}

/**
 * This function creates a table with tabs for navigating through Config Sections (Groups).
 *
 * It analyses a given Array which is in config_data Style:
 *
 * "<group_key>" => array (
 *			"type"          =>
 *			"name"          =>
 *			"default"       =>
 *			"title"		=>
 *			"desc"		=>
 *        )
 *
 * "type"		: Indicates that a group starts or ends. Possible values: 'group_start' , 'group_end'.
 * "name"		: To identify the group you have to set a name.
 * "default"		: Indicates wether the group is visible or not. Possible values: 'inlineÄ', 'none'.
 * "title"		: When the group is visible, this title is displayed in the header line.
 * "desc"		: This optional Description is displayed under the title.
 * ""contains_required"	: Indicates that this Group contains field that are required
 *
 * Note: - The first group which default is 'inline' will the group that is selected when opening the Page.
 *	 - You always need a group_end for a group. Otherwise everything below will belong to the group.
 *
 * @author Jens Tkotz
 */ 

function makeSectionTabs($array, $break = 7, $initialtab = '') {
	$tabs = array();

	foreach ($array as $key => $var) {
        	if(isset($var['type']) && $var['type'] == 'group_start') {
			$tab[]=$var;
		}
	}

	echo "\n<table width=\"100%\" cellspacing=\"0\">";
	echo "\n<tr>";
	$tabcount = 0;
	foreach ($tab as $cell) {
        	$tabcount++;
		if (($cell['default'] == 'inline' && !$initialtab) || $initialtab == $cell['name']) {
		        $class = 'class="tab-hi"';
			if (empty($initialtab)) {
				$initialtab = $cell['name'];
			}
		}
		else { 
			$class = 'class="tab"';
		}
		echo "\n\t<td $class id=\"tab_". $cell['name'] ."\" onClick=\"section_tabs.toggle('" . $cell['name'] ."')\">";
		echo $cell['title'];
		if (!empty($cell['contains_required'])) {
			echo '<span class="littlered">*</span>';
		}
		echo '</td>';
		echo "\n\t<td class=\"tabspacer\">&nbsp;</td>";
		if ($tabcount % $break == 0) {
			echo "\n</tr>\n</table>";
			echo "\n<table width=\"100%\"cellspacing=\"0\" style=\"margin-top:5px;\">\n<tr>";
		}
	}
	echo "\n</tr>";
	echo "\n</table>\n";	

	echo "\n". '<input name="initialtab" id="initialtab" type="hidden" value="'. $initialtab .'">';
	echo "\n". '<script language="JavaScript" type="text/javascript">';

        $i=0;
	echo "\n\t". 'var Sections=new Array()';

        foreach ($array as $key => $var) {
                if(isset($var['type']) && $var['type'] == 'group_start') {
                        echo "\n\tSections[$i] ='". $var['name'] ."' ;";
                        $i++;
                }
        }

	echo "\n\tsection_tabs = new configSection('$initialtab')";
	insertSectionToggle();

	echo "\n</script>\n";
}

function configLogin($target) {
	global $gallery;
	
	if (fs_file_exists(GALLERY_SETUPDIR . "/resetadmin")) {
		$resetFile = getFile(GALLERY_SETUPDIR . "/resetadmin");
		$resetFile = trim($resetFile);
	}
	else {
		// If the file is not present (perhaps removed after visiting the config page)
		// unset the Guid so we don't keep asking for something that's not there.
		$gallery->session->resetAdminGuid = null;
	}
	
	// The gallery has never been configured, or the admin password has been lost and needs to be reset - ignore login requirement
	// If the user has already logged in, and is viewing one of the setup pages, they will be logged in automatically by the form
	if (!isset($gallery->app->userDir) || (isset($gallery->session->resetAdminGuid) && $gallery->session->resetAdminGuid == $resetFile)) {
		return;
	}
	else {
		// Load userDB for password validation - this is a re-configuration
		require_once(GALLERY_BASE . '/classes/User.php');
		require_once(GALLERY_BASE . '/classes/EverybodyUser.php');
		require_once(GALLERY_BASE . '/classes/NobodyUser.php');
		require_once(GALLERY_BASE . '/classes/LoggedInUser.php');
		require_once(GALLERY_BASE . '/classes/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/User.php');
		$gallery->userDB = new Gallery_UserDB();

		// Check the UserDB for upgrades before trying to make someone login
		if ($gallery->userDB->versionOutOfDate()) {
			include(GALLERY_BASE . "/upgrade_users.php");
			exit;
		}

		include(GALLERY_BASE . '/setup/login.inc');
	}
}

function placeholderDescription() {
    $placeholderDescription =  
	_("This email will be sent when new accounts are created.") .
	_("Leaving this field empty sets Gallery to use the default message (see below) which can be translated, or use your own welcome message.") .
	_("The following placeholder can be used:") .
	'<p><table>';
	
    foreach(welcomeMsgPlaceholderList() as $placeholder => $description) {
	$placeholderDescription .= '<tr>'.
				   '<td>!!'. strtoupper($placeholder) . '!!</td>'.
				   "<td>$description</td>".
				   '</tr>';
    }
    $placeholderDescription .= '</table></p>'.

	'<div style="border: 1px black solid; padding-left:10%; padding-right:10%">'. 
		nl2br(welcome_email(true)) . 
	'</div>';

    return $placeholderDescription;
}	
?>
