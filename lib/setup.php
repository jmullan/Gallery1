<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
*/

function evenOdd_row($fields) {
	$html = '';

	$f0 = $fields[0];
	if ($fields[4]) {
		$f0 .= '&nbsp;<span class="g-littlered">*</span>';
	}

	if ($fields[3] == "block_start") {
		$html .= "\n<tr>" .
		"\n\t<td class=\"g-shortdesc\" colspan=\"2\">$f0<p>" . $fields[2] ."</p></td>" .
		"\n</tr>";
	}
	else {
		$html .= "\n<tr>" .
		"\n\t<td class=\"g-shortdesc\" width=\"30%\">$f0</td>" .
		"\n\t<td class=\"g-shortdesc\">$fields[1]</td>" .
		"\n</tr>";

		if (!empty($fields[2])) {
			$html .= "\n\t<tr><td class=\"g-longdesc\" colspan=\"2\">$fields[2]</td></tr>";
		}
	}

	return $html;
}

function make_fields($key, $arr) {
	if (isset($arr['prompt'])) {
		$col1 = $arr['prompt'];
	}
	else {
		$col1 = '';
	}

	if (isset($arr['type']) &&
	  ($arr['type'] == 'text' || $arr['type'] == 'hidden' || $arr['type'] == 'checkbox')) {
		$col2 = form_input($key, $arr);
	}
	else if (isset($arr['choices'])) {
		$col2 = form_choice($key, $arr);
	}
	else if (isset($arr['multiple_choices'])) {
		$col2 = form_multiple_choice($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'textarea') {
		$col2 = form_textarea($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'table_values') {
		$col2 = form_table_values($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'colorpicker') {
		$arr['name'] = $key;
		$col2 = showColorpicker($arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'password') {
		$col2 = form_password($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'nv_pairs') {
		$col2 = form_nv_pairs($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'print_services') {
		$col2 = form_print_services($key, $arr);
	}
	else if (isset($arr['type']) && $arr['type'] == 'byteCalculator') {
		$arr['name'] = $key;
		$col2 = showByteCalculator($key, $arr['value']);
	}
	else {
		$col2 ='';
	}

	if (isset($arr['desc'])) {
		$col3 = $arr['desc'];
	}
	else {
		$col3 = '';
	}

	$col4 = isset($arr['type']) ?  $arr['type'] : NULL;
	$col5 = isset($arr['required']) ? true : NULL;

	return array($col1, $col2, $col3,$col4,$col5);
}

function form_textarea($key, $arr) {
	$attrs = generateAttrs($arr['attrs']);
	$html = "<textarea name=\"$key\" $attrs>{$arr['value']}</textarea>";

	return $html;
}

function form_input($key, $arr) {
	$html = '';

	$name  = (isset($arr['name'])) ? $arr['name'] : $key;

	if($arr['type'] == 'hidden') {
		return "\n<input name=\"$name\" type=\"hidden\" value=\"{$arr['value']}\">";
	}

	$multiInput = false;
	if(!empty($arr['multiInput'])) {
		$arr['attrs']['id'] = $name;
		$name .= '[]';
		$multiInput = true;
	}
	$autocomplete = (isset($arr['autocomplete'])) ? true : false;

	$attrs = (isset($arr['attrs'])) ? $arr['attrs'] : array();

	if(is_array($arr['value'])) {
		foreach ($arr['value'] as $subkey => $value) {
			$html .= gInput($arr['type'], $name, null, false, $value, $attrs, $multiInput,
							$autocomplete);

			if ($multiInput) {
				$html .= gInput($arr['type'], $name, null, false, $value, $attrs, false, false);
			}

			$html .= "\n<br>";
			$multiInput = false;
			$autocomplete = false;
		}
	}
	else {
		$attrs['class'] = 'floatleft';
		$html = gInput($arr['type'], $name, null, false, $arr['value'], $attrs,
					   $multiInput, $autocomplete);
	}

	return $html;

}

function form_password($key, $arr) {
	$attrs = (isset($arr['attrs'])) ? generateAttrs($arr['attrs']) : '';

	if (empty($arr['value'])) {
		$arr['value'] = array('', '', '', '');
	}
	elseif (!is_array($arr['value'])) {
		$arr['value'] = array($arr['value'], $arr['value'], $arr['value'], $arr['value']);
	}

	return "<input type=\"password\" name=\"${key}[0]\" value=\"{$arr['value'][0]}\" $attrs> "
		. '<div style="margin-top: 3px;"></div>'
		. "<input type=\"password\" name=\"${key}[1]\" value=\"{$arr['value'][1]}\" $attrs> "
		. gTranslate('common', "Please retype your password here")
		. "\n<input type=\"hidden\" name=\"${key}[2]\" value=\"{$arr['value'][2]}\">"
		. "\n<input type=\"hidden\" name=\"${key}[3]\" value=\"{$arr['value'][3]}\">";
}

function form_nv_pairs($key, $arr) {
	$attrs = (isset($arr['attrs'])) ? generateAttrs($arr['attrs']) : '';

	$x=0;
	$buf="\n<table>"
		. "<tr>"
		. "<td><b>". gTranslate('common', "Name") . "</b></td>"
		. "<td><b>". gTranslate('common', "Value") ."</b></td>"
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
	$attrs  = !empty($arr['attrs']) ? $arr['attrs'] : array();

	return drawSelect($key, $arr["choices"], $arr["value"], 1, $attrs);
}

function form_multiple_choice($key, $arr) {
	if (empty($arr["multiple_choices"])) {
		return gTranslate('common', "No content");
	}

	$buf = '<table><tr><td valign="top">';
	$count = 0;
	$column = 0;
	foreach ($arr["multiple_choices"] as $item => $value) {
		if ($item == 'addon') {
			continue;
		}

		if ($count%15 == 0) {
			$buf .= "</td>\n<td valign=\"top\">";
		}

		$count++;
		$column++;
		$selected = '';

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
		return gTranslate('common', "No content");
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
	$html = "\n\t<table border=\"0\">";

	foreach ($arr['services'] as $item => $data) {
		if (isset($arr['value'][$item])) {
			if (is_array($arr['value'][$item])) {
				$value = $arr['value'][$item];
				if (!isset($value['checked'])) {
					$value['checked'] = false;
				}
			}
			else {
				$value = array('checked' => true);
			}
		}
		else {
			$value = array('checked' => false);
		}

		$checked = $value['checked'] ? ' checked' : '';
		$html .= "\n\t\t<tr><td valign=\"top\">\n\t\t\t<input name=\"${key}[$item][checked]\" value=\"checked\" type=\"checkbox\"$checked><a href=\"${data['url']}\">${data['name']}</a>";

		if (!empty($data['desc'])) {
			$html .= ' - ' . $data['desc'];
		}
		$html .= "\n\t\t</td></tr>";
	}

	$html .="\n\t</table>\n\t";

	return $html;
}

/**
 * Returns an array containing all pathes set in the environment var 'PATH'
 * plus some additional guesses.
 *
 * @return array	$path
 */
function getPath() {
	static $path;

	if(!empty($path)) {
		return $path;
	}

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

/**
 * Tries to locate a file at various places
 *
 * @param string	$filename		The file to find
 * @param string	$extraDir		Tries especially to find the file in that folder
 * @param boolean	$ignorePath		? FIXME ?
 * @return strng 	$dir			'' if not found, otherwise the path where the file is.
 */
function locateDir($filename, $extraDir = '', $ignorePath = false) {
	$dir = '';

	if (fs_file_exists("$extraDir/$filename")) {
		$dir = $extraDir;
	}
	elseif ($ignorePath) {
		$dir =  '';
	}
	else {
		foreach (getPath() as $path) {
			if (fs_file_exists("$path/$filename") && !empty($path)) {
				$dir = $path;
				break;
			}
		}
	}

	return $dir;
}

function locateFile($filename) {
	$file = null;

	foreach (getPath() as $path) {
		if (fs_file_exists("$path/$filename") && !empty($path)) {
			$file = "$path/$filename";
			break;
		}
	}

	return $file;
}

function one_constant($key, $value) {
	return "\$gallery->app->$key = \"{$value}\";\n";
}

function array_constant($key, $value, $removeEmpty = false) {
	$html = '';

	foreach ($value as $item) {
		if($removeEmpty && empty($item)) {
			continue;
		}
		else {
			$html .= "\$gallery->app->${key}[] = \"{$item}\";\n";
		}
	}

	return $html;
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
		$desc = $key;
	}
	return configError(sprintf(gTranslate('common', "Missing value: %s"),"<b>$desc</b>!"));
}

function check_exec() {
	$disabled = "" . ini_get("disable_functions");

	$success	= array();
	$fail		= array();
	$warn		= array();

	if (!empty($disabled)) {
		foreach(explode(',', $disabled) as $disabled_func) {
			if(eregi('^exec$', $disabled_func)) {
				$fail['fail-exec'] = 1;
			}
		}
	}

	if (empty($fail['fail-exec'])) {
		$success[] = gTranslate('common', "exec() is enabled on this server.");
	}

	return array($success, $fail, $warn);
}

/**
 * the .htaccess file in the parent directory tries to auto_prepend the got-htaccess.php file.
 * If that worked, then GALLERY_PHP_VALUE_OK will be set.
*/
function check_htaccess() {
	global $GALLERY_PHP_VALUE_OK;

	$success	= array();
	$fail		= array();
	$warn		= array();

	if(!fs_file_exists(GALLERY_SETUPDIR .'/.htaccess')) {
		$fail['fail-nohtaccess'] = true;
	}
	else if ($GALLERY_PHP_VALUE_OK) {
		$success[] = gTranslate('common', "Gallery is able to read your .htaccess file.");
	}
	else {
		$fail['fail-htaccess'] = true;
	}

	return array($success, $fail, $warn);
}

function check_php() {
	global $MIN_PHP_MAJOR_VERSION;

	$version	= phpversion();
	$success	= array();
	$fail		= array();
	$warn		= array();

	if (!function_exists('version_compare') ||
		!version_compare($version, $MIN_PHP_MAJOR_VERSION, ">=")) {
		$fail['fail-too-old'] = 1;
	}
	elseif (strstr(__FILE__, 'lib/setup.php') || strstr(__FILE__, 'lib\\setup.php')) {
		$success[] = sprintf(gTranslate('common', "PHP v%s is OK."), $version);
	}
	else {
		$fail['fail-buggy__FILE__'] = 1;
	}

	return array($success, $fail, $warn);
}

function check_mod_rewrite()  {
	global $GALLERY_REWRITE_OK;

	$success	= array();
	$fail		= array();
	$warn		= array();

	if(fs_file_exists(GALLERY_SETUPDIR .'/.htaccess')) {
		if ($GALLERY_REWRITE_OK) {
			$success[] = gTranslate('common', "mod_rewrite is enabled.");
		}
		else {
			$fail["fail-mod-rewrite"] = 1;
		}
	}
	else {
		$fail["fail-mod-rewrite-nohtaccess"] = 1;
	}

	return array($success, $fail, $warn);
}

function check_exif($location = '') {
	global $gallery;

	$success	= array();
	$fail		= array();
	$warn		= array();

	$bin = fs_executable('jhead');

	if ($location) {
		$dir = locateDir($bin, $location);
	}
	else {
		$dir = locateDir($bin, isset($gallery->app->use_exif) ? dirname($gallery->app->use_exif) : "");
	}

	$jheadVersion = getJheadVersion($dir);

	if (empty($dir)) {
		$warn["warn-noexif"] = gTranslate('common', "Can't find <i>jhead</i>.");
	}
	elseif(compareVersions($jheadVersion, '2.7') > 0) {
		$fail["fail-exif-old"] =
			sprintf(gTranslate('common', "<b>jhead</b> binary version %s located."), $jheadVersion) . '<br>' .
			gTranslate('common', "You are using an older version of jhead. There are at least known problems with version 2.0. We recommend version 2.7 and higher.");
	}
	else {
		$success[] = sprintf(gTranslate('common', "<b>jhead</b> binary version %s located."), $jheadVersion);
	}

	return array($success, $fail, $warn);
}

function check_graphics($location = '', $graphtool = '') {
	global $gallery;

	$success	= array();
	$fail		= array();
	$warn		= array();

	$missing_critical = array();
	$missing = 0;
	$netpbm = array(
		fs_executable('jpegtopnm'),
		fs_executable('giftopnm'),
		fs_executable('pngtopnm'),
		fs_executable('pnmtojpeg'),
		fs_executable('ppmtogif'),
		fs_executable('pnmtopng'),
		fs_executable('pnmscale'),
		fs_executable('pnmfile'),
		fs_executable('ppmquant'),
		fs_executable('pnmcut'),
		fs_executable('pnmrotate'),
		fs_executable('pnmflip'),
		fs_executable('pnmcomp'),
	);

	$fallback = array(
		fs_executable('pnmtojpeg') => fs_executable('ppmtojpeg'),
		fs_executable('pnmcomp')   => fs_executable('pamcomp')
	);

	$optional = array(
		fs_executable('pnmcomp') =>
			gTranslate('common', "Without pnmcomp and pamcomp, gallery will not be able to watermark images, unless you use ImageMagick and have the composite binary installed."),
	);

	$missing_optional = 0;

	/* Start checks */

	if ($graphtool == 'ImageMagick') {
		$success[] = gTranslate('common', "Netpbm not being used in this installation.");
		return array($success, $fail, $warn);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
		$warn[] = gTranslate('common', "Cannot verify this path (it's not in your open_basedir list).");
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
				$warn[$bin] = '<br>'. sprintf(gTranslate('common', "Missing optional binary %s. %s"), $bin, $optional[$bin]);
			}
			else {
				$missing_critical[$bin] = '<br>'. sprintf(gTranslate('common', "Can't find %s!"), "<i>$bin</i>");
			}
			$missing++;
		}

		if (!empty($dir) && inOpenBasedir($dir) && !fs_is_executable("$dir/$bin")) {
			$warn[$bin] = '<br>'. sprintf(gTranslate('common', "%s is not executable!"), "<i>$bin</i> ");
		}
	}

	if ($missing == count($netpbm)) {
		$fail['fail-netpbm'] = 1;
		/* Any other warning doesnt care */
		$warn = array();
	}
	elseif ($missing > 0) {
		$warn[] = "\n<br>" . sprintf(gTranslate('common', "%d of %d Netpbm binaries located."),
		count($netpbm) - $missing, count($netpbm));

		if(count($missing_critical) > 0) {
			$fail['fail-netpbm-partial'] = array_values($missing_critical);
		}
	}
	else {
		$success[] = sprintf(gTranslate('common', "%d of %d Netpbm binaries located."),
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
		fs_executable('identify'),
		fs_executable('convert'),
		fs_executable('composite'),
	);

	$optional = array(
		fs_executable('composite') =>
		gTranslate('common', "Without composite gallery will not be able to watermark images, except you use Netpbm and have the pnmcomp binary installed."),
	);


	/* Begin Checks */
	if ($graphtool == 'Netpbm') {
		$success[] = gTranslate('common', "ImageMagick not being used in this installation.");
		return array($success, $fail, $warn);
	}

	if (!empty($location) && !inOpenBasedir($location)) {
		$success[] = gTranslate('common', "Cannot verify this path (it's not in your open_basedir list).");
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
				$warn[$bin] = '<br>'. sprintf(gTranslate('common', "Missing optional binary %s. %s"), $bin, $optional[$bin]);
			}
			else {
				$missing_critical[$bin] = '<br>'. sprintf(gTranslate('common', "Can't find %s!"), "<i>$bin</i>");
			}
			$missing++;
		}

		if (!empty($dir) && inOpenBasedir($dir) && !fs_is_executable("$dir/$bin")) {
			$warn[$bin] = '<br>'. sprintf(gTranslate('common', "%s is not executable!"), "<i>$bin</i> ");
		}
	}

	if ($missing == count($imagick)) {
		$fail['fail-imagemagick'] = 1;
		/* Any other warning doesnt care */
		$warn = array();
	}
	elseif ($missing > 0) {
		$warn[] = '<br>'. sprintf(gTranslate('common', "%d of %d ImageMagick binaries located."),
		count($imagick) - $missing, count($imagick));

		if(count($missing_critical) > 0) {
			$fail['fail-imagemagick-partial'] = array_values($missing_critical);
		}
	}
	else {
		$success[] = sprintf(gTranslate('common', "%d of %d ImageMagick binaries located."),
		count($imagick), count($imagick));
	}

	return array($success, $fail, $warn);
}

function check_jpegtran($location = '') {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$bin = fs_executable('jpegtran');

	if ($location) {
		$dir = locateDir($bin, $location);
	}
	else {
		$dir = locateDir($bin, isset($gallery->app->use_jpegtran) ? dirname($gallery->app->use_jpegtran) : '');
	}

	if (!$dir) {
		$warn["fail-jpegtran"] = gTranslate('common', "jpegtran was not found.");
	}
	else {
		$success[] = gTranslate('common', "jpegtran binary located.");
	}

	return array($success, $fail, $warn);
}

function check_gettext() {
	$fail = array();
	$success = array();
	$warn = array();

	if (gettext_installed()) {
		$success[] = gTranslate('common', "PHP has GNU gettext support.");
	} else {
		$warn["fail-gettext"] = gTranslate('common', "PHP does not have GNU gettext support.");
	}
	return array($success, $fail, $warn);
}

function check_gallery_languages() {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$languages = gallery_languages();

	if (sizeof($languages) == 0) {
		$fail["fail-gallery-languages"] = gTranslate('common', "No languages found."); // should never occur!
	}
	else if (sizeof($languages) == 1 ) {
		$warn['only_english'] = gTranslate('common', "It seems you didn't download any additional languages. This is not a problem! Gallery will appear just in English. Note: If this is not true, check that all files in locale folder are readable for the webserver, or contact the Gallery Team.");
	}
	else {
		$success[] = sprintf(gTranslate('common', "%d languages are available.  If you are missing a language please visit the %sGallery download page%s."),
				sizeof($languages),
				"<a href=\"$gallery->url\" target=\"_blank\">",
				'</a>');
	}

	return array($success, $fail, $warn);
}

function check_gallery_version() {
	global $gallery;

	$fail = array();
	$success = array();
	$warn = array();

	$maxAge = 180;
	$maxBetaAge = 14;

	/* how many days old is the gallery version? */
	$age = (time() - $gallery->last_change)/86400;

	/* is this a beta or RC version? */
	$beta = ereg('-(b|RC)[0-9]*$', $gallery->version);

	$link = galleryLink($gallery->url, $gallery->url, array('target' => '_blank'));

	$visit = sprintf(gTranslate('common', "You can check for more recent versions by visiting %s."), $link);

	$this_version = sprintf(gTranslate('common', "This version of %s was released on %s."),
		Gallery(), strftime("%x", $gallery->last_change));

	$this_beta_version = sprintf(gTranslate('common', "This is a development build of %s that was released on %s."),
		Gallery(), strftime("%x", $gallery->last_change));

	if ($age > $maxAge) {
		if($beta) {
			$fail['too_old'] = $this_beta_version . ' ' .
							   sprintf(gTranslate('common', "That's more than %d days ago. Which is way too old for a pre Release version."), $maxAge) .
							   toggleBox('g_version', $visit);
		}
		else {
		  $fail['too_old'] = $this_version . ' ' .
						   sprintf(gTranslate('common', "That's more than %d days ago."), $maxAge) .
						   toggleBox('g_version', $visit);
		}
	}
	else if ($beta && $age > $maxBetaAge) {
		$fail['too_old'] = $this_beta_version . toggleBox('g_version', $visit);
	}
	else if ($beta) {
		$visit .= ' '. gTranslate('common', "Please check regularly for updates.");
		$success['ok'] = $this_beta_version . toggleBox('g_version', $visit);
	}
	else {
		$success['ok'] = $this_version . toggleBox('g_version', $visit);
	}

	return array($success, $fail, $warn);
}

function check_absent_locales() {
	global $locale_check;

	$fail = array();
	$success = array();
	$warn = array();
	$msg = '';

	$available = $locale_check['available_locales'];
	$maybe = $locale_check['maybe_locales'];
	$unavailable = $locale_check['unavailable_locales'];

	if($locale_check != NULL && sizeof($unavailable) == 0) {
	   $success[] = gTranslate('common', "All gallery locales are available on this host.");
	}
	else if( (sizeof($maybe) + sizeof($unavailable)) > 0) {
		if (sizeof($maybe) > 0) {
			$msg = sprintf(gTranslate('common', "There are %d locales that Gallery was unable to locate. You may need to select manually date formats. "),sizeof($maybe));
		}

		if (sizeof($unavailable) > 0) {
			if(sizeof($maybe) > 0) {
				$msg .= '<br><br>';
			}

			$msg .= sprintf(gTranslate('common', "Dates in %d languages may not be formatted properly, because the corresponding locales are missing. You may need to select manually the date formats for these."),sizeof($unavailable));
		}
		$warn[] = $msg;
	}
	else {
		if (ini_get('open_basedir') && getOS() != OS_LINUX) {
			$warn[] = sprintf(gTranslate('common', "We were unable to detect any locales.  However, your PHP installation is configured with the %s restriction so this may be interfering with the way that we detect locales.  Unfortunately this means the date format will not change for different languages.  However, it is OK to continue."),
				'<b><a href="http://www.php.net/manual/en/features.safe-mode.php#ini.open-basedir" target="_blank">open_basedir</a></b>');
		}
		else {
			if (getOS() == OS_LINUX) {
				$fail[] = sprintf(gTranslate('common', "We were unable to detect any system locales. Multi-language functions will be disabled. Please install the corresponding locales or ask your administrator to do this. This problem is known on %s systems. In this case please have a look at this %sDebian locale HowTo%s."),
				'Debian',
				'<a href="http://people.debian.org/~schultmc/locales.html" target="_blank">', '</a>');
			}
			else {
				$warn[] = gTranslate('common', "Only the default locale for this machine is available, so date format will not change for different languages.");
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

		exec('locale -a', $results, $status);

		if(count($results) >2) {
			$system_locales = $results;
		}
		elseif (@is_readable("/etc/locale.gen")) {
			exec('grep -v -e "^#" /etc/locale.gen | cut -d " " -f 1', $system_locales);
		}
		elseif (@is_readable("/usr/share/locale")) {
			exec("ls /usr/share/locale", $system_locales);
		}
		elseif (@is_readable("/usr/local/share/locale")) {
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
		 * First, we try using the full lang, (first 5 chars) if
		 * that doesn't match then
		 * we use the first 2 letter to build an alias list
		 * e.g. nl to find nl_BE or nl_NL
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
		}
		else {
			foreach ($system_locales as $key => $value) {
				if (ereg('^' . substr($locale,0,2), $value)) {
					$aliases[] = $value;
				}
			}
		}

		$aliases	= array_unique($aliases);
		$noway		= array('zh_TW.eucTW');
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
		}
		else {
			$unavailable_locales[] = $locale;
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
		'available_locales'   => $available_locales,
		'maybe_locales'		  => $maybe_locales,
		'unavailable_locales' => $unavailable_locales
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
	if($locales == NULL) {
		return array();
	}

	$nls = getNLS();

	$block_start_done = false;

	$nr = 0;
	foreach ($maybe as $key => $aliases) {
		if (sizeof($aliases) == 0) {
			$unavailable[] = $key;
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
			$block_start_done = true;
			$results[] = array (
				'type' => 'block_start',
				'prompt' => '<b>(' . gTranslate('common', "Advanced") . ') </b><br>' .
					sprintf(gTranslate('common', "<b>System</b> locale selection required")),
						'desc' => gTranslate('common', "There is more than one suitable <b>system</b> locale installed on your machine for the following languages.  Please chose the one you think is most suitable.") .
						'<br><br>' .
					gTranslate('common', "This is <b>only</b> for date &amp; time format. You only need to edit the languages you enabled above.")
			);
		}

		$index = $nls['language'][$key];

		$choices = array();

		foreach ($aliases as $value) {
			$choices[$value] = $value;
		}

		if (getOS() != OS_WINDOWS) {
			$choices[''] = gTranslate('common', "System locale");
			next($choices);
		}

		$results["locale_alias['$key']"] = array (
			'prompt' => $nr . '.) ' . $nls['language'][$key],
			'optional' => true,
			'name' => 'locale_alias',
			'key' => $key,
			'type' => 'block_element',
			'choices' => $choices,
			'value' => (getOS() != OS_WINDOWS) ? key($choices) : '',
			'allow_empty' => true,
			'remove_empty' => true
		);


	} // End foreach maybe

	if ($block_start_done) {
		$results[] = array ('type' => 'block_end');
	}

	$block_start_done = false;

	$choices = array();

	if (getOS() != OS_WINDOWS) {
		$choices = array('' => gTranslate('common', "System locale"));
	}

	if (sizeof($available) > 0) {
		foreach ($available as $choice => $value) {
			$choices[$choice] = $nls['language'][$value];
		}

		$avail_keys = array_keys($available);
	}
	elseif (sizeof($maybe) > 0) {
		foreach ($maybe as $key => $aliases) {
			foreach ($aliases as $choice) {
				$choices[$choice] = $choice;
			}
		}

		$avail_keys = array_keys($choices);
	}
	else {
		if (getOS() == OS_OTHER) {
			$array_keys = $choices;
		}
		else {
			$skip = true;
		}
	}

	if (! isset ($skip)) {
		$avail_keys = array_keys($choices);
		foreach ($unavailable as $key) {
			if (sizeof($choices) == 1) {
				$results["locale_alias['$key']"] = array (
					'type' => 'hidden',
					'value' => $avail_keys[0],
					'desc' => "locale_alias[$key]",
					'prompt' => "locale_alias[$key]",
					'allow_empty' => true,
					'remove_empty' => true
				);
				continue;
			}

			if (!$block_start_done) {
				$block_start_done = true;
				$results[] = array (
					'type' => 'block_start',
					'prompt' => '<b>(' . gTranslate('common', "Advanced") . ')</b><br>' .
						sprintf(gTranslate('common', "<b>System</b> locale problems")),
							'desc' => gTranslate('common', "There are no apparently suitable <b>system</b> locales installed on your machine for the following languages.  Please choose the one you think is most suitable.") .
							'<br><br>' .
						gTranslate('common', "This is <b>only</b> for date &amp; time format. You only need to edit the languages you enabled above.")
				);
			}

			$index = $nls['language'][$key] ;

			$results["locale_alias['$key']"] = array (
				'prompt' => $nls['language'][$key],
				'name' => 'locale_alias',
				'key' => $key,
				'type' => 'block_element',
				'choices' => $choices,
				'value' => '',
				'allow_empty' => true,
				'remove_empty' => true
			);
		}

		if ($block_start_done) {
			$results[] = array ('type' => 'block_end');
		}
	}

	return $results;
}

function default_graphics() {
	list ($imageMagick,) = check_graphics_im();

	if (count ($imageMagick)) {
		return "ImageMagick";
	}
	else {
		return "Netpbm";
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
		$success[] = gTranslate('common', "safe_mode is off.");
	}
	else {
		$fail["fail-safe-mode"] = 1;
	}

	return array($success, $fail,$warn);
}

function check_magic_quotes() {
	$fail = array();
	$success = array();
	$warn = array();
	if (!get_magic_quotes_gpc()) {
		$success[] = gTranslate('common', "magic_quotes are off.");
	} else {
		$fail["fail-magic-quotes"] = 1;
	}

	return array($success, $fail, $warn);
}

function check_poll_nv_pairs($var) {
	$fail = array();
	$success = array();
	$finished = false;
	$rownum = 0;
	foreach ($var as $element) {
		$rownum++;
		if (!$element["name"]) {
			$finished=true;
			if ($element["value"]) {
				$fail[]=sprintf(gTranslate('common', "In %s, missing %s in row %d with %s %s."),
					gTranslate('common', "Vote words and values"),
					gTranslate('common', "Name"), $rownum, gTranslate('common', "Value"),
					$element["value"]);
				break;
			}
			continue;
		}
		else {
			if ($finished) {
				$fail[] = sprintf(gTranslate('common', "In %s, blank in row %d."),
					gTranslate('common', "Vote words and values"),
					$rownum-1);
				break;
			}
			else if (!ereg("^[1-9][0-9]*$", $element["value"])) {
				$fail[] = sprintf(gTranslate('common', "In %s, for name %s (row %d) value %s should be a positive whole number"),
					gTranslate('common', "Vote words and values"),
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
		$success[] = gTranslate('common', "register_globals is off.");
	}

	return array($success, $fail, $warn);
}

/**
 * Try to detect the return value for a succesfull system function call
 *
 * @return integer
 */
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
				}
				else {
					$count[$status] = 1;
				}
			}
		}
	}

	if (count($count) == 0) {
		// Nothing!  :-(  Hope for the best.
		return 0;
	}
	else {
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

function array_stripslashes($subject) {
	if (is_string($subject)) {
		return stripslashes($subject);
	}

	if (!is_array($subject)) {
		return ($subject);
	}

	$ret = array();
	foreach ($subject as $key => $value) {
		$ret[$key] = array_stripslashes($value);
	}

	return $ret;
}

/*
 * Check if Magic Quotes are On
 * If yes stripslashes and return the cleaned input.
 *
 * Jens Tkotz, 02/2004
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

	$ret = array();

	foreach ($subject as $key => $value) {
		$ret[$key] = array_urldecode($value);
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

	$ret = array();
	foreach ($subject as $key => $value) {
		$ret[$key] = array_str_replace($search, $replace, $value);
	}

	return $ret;
}

function verify_password($passwords) {
	$success = array();
	$fail = array();

	if ($passwords[2] === $passwords[3]) {
		$success[] = true;
	}
	else {
		$fail[] = gTranslate('common', 'Your passwords do not match!');
	}

	return array($success, $fail);
}

function verify_email($emailMaster) {
	global $gallery;

	$fail = array();
	$success = array();
	if ($emailMaster == "no") {
		$success[] = gTranslate('common', "OK");
		return array($success, $fail);
	}

	if (check_email($gallery->session->configForm->adminEmail)) {
		$success[] = gTranslate('common', "Valid admin email address given.");
	}
	else {
		$adminEmail = ereg_replace('([[:space:]]+)', '', $gallery->session->configForm->adminEmail);
		$emails = array_filter1(explode(',', $gallery->session->configForm->adminEmail));
		$size  = sizeof($emails);

		if ($size < 1) {
			$fail[]= gTranslate('common', "You must specify valid admin email addresses.");
		}
		else {
			$adminEmail = '';
			$join = '';
			foreach ($emails as $email) {
				$adminEmail .= "$join$email";
				$join=",";
				if (! check_email($email)) {
					$fail[] = sprintf(gTranslate('common', "%s is not a valid email address."),
					$email);
				}
				else {
					$success[] = "Valid admin email given.";
				}
			}
		}
	}

	if (check_email($gallery->session->configForm->senderEmail)) {
		$success[] = gTranslate('common', "Valid sender email address given.");
	}
	else {
	       	$fail[] = gTranslate('common', "You must specify a valid sender email address.");
	}

	if (!empty($gallery->session->configForm->emailGreeting) && !strstr($gallery->session->configForm->emailGreeting, "!!USERNAME!!")) {
	       	$fail[] = sprintf(gTranslate('common', "You must include %s in your welcome email."), "<b>!!USERNAME!!</b>");
	}

	if (!empty($emailGreeting) &&
	  !strstr($gallery->session->configForm->emailGreeting, "!!PASSWORD!!" ) &&
	  !strstr($gallery->session->configForm->emailGreeting, "!!NEWPASSWORDLINK!!" )) {
	       	$fail[]= sprintf(gTranslate('common', "You must include %s or %s in your welcome email."),
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

function returnToConfig() {
	$link = galleryLink(
		makeGalleryUrl('setup/index.php', array('refresh' => 1, 'this_page' => 'check')),
		gTranslate('common', "_Configuration Wizard"), array(), '', true);

	return $link;
}

function returnToDiag() {
	$link = galleryLink(
		makeGalleryUrl('setup/diagnostics.php'),
		gTranslate('common', "Gallery Dia_gnostics Page"), array(), '', true);

	return $link;
}

if (!function_exists('array_filter1')) {
	function array_filter1($input, $function=NULL) {
		$output = array();
		foreach ($input as $name => $value) {
			if ($function && $function($value)) {
				$output[$name] = $value;
			}
			else if ($value) {
				$output[$name] = $value;
			}
		}

		return $output;
	}
}

function check_admins() {
	global $gallery;

	$admins = array();

	if (isset($gallery->app->userDir) && fs_is_dir($gallery->app->userDir)) {
		require_once(GALLERY_BASE . '/classes/User.php');
		require_once(GALLERY_BASE . '/classes/EverybodyUser.php');
		require_once(GALLERY_BASE . '/classes/NobodyUser.php');
		require_once(GALLERY_BASE . '/classes/LoggedInUser.php');
		require_once(GALLERY_BASE . '/classes/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/UserDB.php');
		require_once(GALLERY_BASE . '/classes/gallery/User.php');


		$userDB = new Gallery_UserDB();

		$admins = array();
		if (isset($userDB)) {
			foreach ($userDB->getUidList() as $uid) {
				$tmpUser = $userDB->getUserByUid($uid, true);

				if ($tmpUser->isAdmin()) {
					$admins[] = $tmpUser->getUsername();
				}
			}
		}
	}

	if (empty($admins)) {
		$result = array(
			'desc' => sprintf(gTranslate('common', 'You must enter a password for the %s account.'), '<b>admin</b>')
		);
	}
	else if (! in_array("admin",$admins)) {
		if (sizeof($admins) == 1) {
			$desc_text=sprintf(gTranslate('common', "It seems you've already configured Gallery, because there is one admin account, but it is not called %s."), '<b>admin</b>');
		}
		else {
			$desc_text = sprintf(gTranslate('common', "It seems you've already configured Gallery, because there are %d admin accounts, but no user called %s."), sizeof($admins), '<b>admin</b>');
		}

		$desc_text .= "  " . sprintf (gTranslate('common', "You don't have to enter a password.  But if you do, Gallery will create an administrator account called %s with that password."), '<b>admin</b>');
		$result=array(
			"desc" => $desc_text,
			"optional" => 1,
			"remove_empty" => true
		);
	}
	else {
		$result = array(
			"desc" => sprintf(gTranslate('common', "It seems you've already configured Gallery, because the %s user exists.  You don't have to enter a password.  But if you do, Gallery will change the password for the %s user."), '<b>admin</b>', '<b>admin</b>'),
			"remove_empty" => true
		);
	}

	$result = array_merge($result,array(
		"prompt" => gTranslate('common', "Admin password"),
		"type" => "password",
		"dont-write" => 1,
		'verify-func' => 'verify_password',
		"value" => "",
		"attrs" => array("size" => 20),
		"required" => true
		)
	);

	return $result;
}

function displayNameOptions() {
	return array (
		"!!FULLNAME!! (!!USERNAME!!)" => sprintf("%s (%s)", gTranslate('common', "Full Name"), gTranslate('common', "Username")),
		"!!USERNAME!! (!!FULLNAME!!)" => sprintf("%s (%s)", gTranslate('common', "Username"), gTranslate('common', "Full Name")),
		"!!FULLNAME!!" => gTranslate('common', "Full Name"),
		"!!USERNAME!!" => gTranslate('common', "Username"),
		"!!MAILTO_FULLNAME!!" => gTranslate('common', "Full name that you can click on to send email (mailto:)"),
		"!!MAILTO_USERNAME!!" => gTranslate('common', "Username that you can click on to send email (mailto:)"),
		"!!FULLNAME!! (!!EMAIL!!)" => sprintf("%s (%s)", gTranslate('common', "Full Name"), gTranslate('common', "email address")),
		"!!USERNAME!! (!!EMAIL!!)" => sprintf("%s (%s)", gTranslate('common', "Username"), gTranslate('common', "email address")),
	);
}

function check_gallery_versions()  {
	$fail = array();
	$success = array();
	$warn = array();

	$versionStatus = checkVersions(false);

	$fail = $versionStatus['fail'];

	$problems = array_merge(
		$versionStatus['missing'],
		$versionStatus['older'],
		$versionStatus['unkown']
	);

	$hint = "<p>" . gTranslate('common', "This should be fixed before proceeding.") .
		"<br>" . sprintf(gTranslate('common', "Look at %sCheck Versions%s for more details."),
		'<a href="check_versions.php">', '</a>') . '</p>';

	if(!empty($versionStatus['newer'])) {
		$warn[] = gTranslate('common',
			"One file is newer then expected.",
			"%d files are newer then expected.",
			count($versionStatus['newer']), '', true) .
			$hint;
	}

	if(!empty($problems)) {
		$fail[] = gTranslate('common',
			"One file is out of date, corrupted or missing.",
			"%d files are out of date, corrupted or missing.",
			count($problems), '', true) .
			(empty($versionStatus['newer']) ? $hint : '<br>');
	}

	if (empty($warn) && empty($fail)) {
		$success[] = sprintf(gTranslate('common', "All %d tested files up-to-date."), count($versionStatus['ok']));
	}

	return array($success, $fail, $warn);
}

function checkVersions($verbose = false) {
	global $gallery;
	/* we assume setup/init.php was loaded ! */

	$manifest = GALLERY_BASE . '/manifest.inc';
	$versionStatus = array(
		'ok'		=> array(),
		'fail'		=> array(),
		'older'		=> array(),
		'newer'		=> array(),
		'missing'	=> array(),
		'unkown'	=> array()
	);

	if (!fs_file_exists($manifest)) {
		$versionStatus['fail']['manifest'] = gTranslate('common', "The manifest file is missing or unreadable.  Gallery is not able to perform a file version integrity check.  Please install this file and reload this page.");
		return $versionStatus;
	}

	if (!function_exists('getSVNRevision')) {
		$versionStatus['fail']['util.php'] = sprintf(gTranslate('common', "Please ensure that %s is the latest version. Gallery is not able to perform a file version integrity check.  Please install the correct version and reload this page."), "util.php");
		return $versionStatus;
	}

	include (GALLERY_BASE . '/manifest.inc');

	if ($verbose) {
		print sprintf(gTranslate('common', "Testing status of %d files."), count($versions));
	}

	foreach ($versions as $file => $version) {
		$found_version = getSVNRevision($file);
		if ($found_version === NULL) {
			if ($verbose) {
				print "<br>\n";
				print sprintf(gTranslate('common', "Cannot read file %s."), $file);
			}
			$fail[$file] = gTranslate('common', "File missing or unreadable.");
			continue;
		}
		elseif ($found_version === '' ) {
			if (preg_match('/(\.jpg|\.png|\.gif|\.jar|\.mo|\.ico|Changelog|.ttf)$|^includes\/ecard\/templates/i', $file, $matches)) {
				if($verbose) {
					echo "<br>\n";
					printf("File with type: %s can not have a compareable Revision Nr.", $matches[0]);
					continue;
				}
				$versionStatus['ok'][$file] = sprintf("No comparable Rev for type: %s", $matches[0]);
				continue;
			}
			else {
				if ($verbose) {
					print "<br>\n";
					printf(gTranslate('common', "Version information not found in %s.  File must be old version or corrupted."), $file);
				}
				$versionStatus['missing'][$file] = gTranslate('common', "Missing version");
				continue;
			}
		}
		elseif (empty($version)) {
			$versionStatus['unkown'][$file] = sprintf(gTranslate('common',
				"Found Version: %s"), $found_version);
			continue;
		}

		$compare = compareVersions($version, $found_version);

		if ($compare < 0) {
			if ($verbose) {
				print "<br>\n";
				printf(gTranslate('common', "Problem with %s.  Expected version %s (or greater) but found %s."), $file, $version, $found_version);
			}
			$versionStatus['older'][$file] =
				sprintf(gTranslate('common', "Expected version %s (or greater) but found %s."), $version, $found_version);
		}
		else if ($compare > 0) {
			if ($verbose) {
				print "<br>\n";
				printf(gTranslate('common', "%s OK.  Actual version (%s) more recent than expected version (%s)."), $file, $found_version, $version);
			}
			$versionStatus['newer'][$file] =
				sprintf(gTranslate('common', "Expected version %s but found %s."), $version, $found_version);
		}
		else {
			if ($verbose) {
				print "<br>\n";
				printf(gTranslate('common', "%s OK."), $file);
			}
			$versionStatus['ok'][$file] = sprintf(gTranslate('common', "Found expected version %s."), $version);
		}
	}

	return $versionStatus;
}

function configLogin($target) {
	global $gallery;

	if (fs_file_exists(GALLERY_SETUPDIR . "/resetadmin")) {
		$resetFile = fs_file_get_contents(GALLERY_SETUPDIR . "/resetadmin");
		$resetFile = trim($resetFile);
	}
	else {
		// If the file is not present (perhaps removed after visiting the config page)
		// unset the Guid so we don't keep asking for something that's not there.
		$gallery->session->resetAdminGuid = null;
	}

	// The gallery has never been configured, or the admin password has been lost and needs to be reset - ignore login requirement
	// If the user has already logged in, and is viewing one of the setup pages, they will be logged in automatically by the form
	if (!isset($gallery->app->userDir) ||
	   (isset($gallery->session->resetAdminGuid) && $gallery->session->resetAdminGuid == $resetFile)) {
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

		if (! $gallery->userDB->isInitialized()) {
			exit;
		}

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
		gTranslate('common', "This email will be sent when new accounts are created.") . '  ' .
		gTranslate('common', "Leaving this field empty sets Gallery to use the default message (see below) which can be translated, or use your own welcome message.") . '  ' .
		gTranslate('common', "The following placeholder can be used:") .
		'<table width="80%">';

	foreach(welcomeMsgPlaceholderList() as $placeholder => $description) {
		$placeholderDescription .= '<tr>'.
			'<td>!!'. strtoupper($placeholder) . '!!</td>'.
			"<td>$description</td>".
			'</tr>';
	}

	$placeholderDescription .= '</table><br>'.

	'<fieldset><legend>' . gTranslate('common', "Current used welcome mail text") .'</legend>' .
		nl2br(welcome_email(true)) .
	'</fieldset>';

	return $placeholderDescription;
}

/**
 * Returns a status code base on the given resultset
 *
 * 0 - success
 * 5 - warning, optional
 * 10 - serious warning, but optional
 * 51 - serious warning
 * 100 - failure
 *
 * @param array $result	 The resultcheck
 * @param array $check
 * @return integer
 * @author Jens Tkotz
 */
function getCheckStatus($result, $check) {
	list($success, $fail, $warn) = $result;

	if(!empty($success)) {
		return 0;
	}

	if (isset($check['optional']) && $check['optional'] == 1) {
		if (isset($check["serious"]) && $check["serious"] == 1) {
			if(empty($fail)) {
				return 5;
			}
			else {
				return 10;
			}
		}
		else {
			return 5;
		}
	}
	else {
		if (isset($check["serious"]) && $check["serious"] == 1) {
			return 51;
		}
		else {
			return 100;
		}
	}

}

function checkImageMagick($cmd) {
	global $gallery;
	global $debugfile;

	$status = '';
	$cmd = fs_executable($gallery->app->ImPath . "/$cmd");
	$result[] = fs_import_filename($cmd);
	$ok = true;

	if (inOpenBasedir($gallery->app->ImPath)) {
		if (! fs_file_exists($cmd)) {
			$result['error'] = sprintf(gTranslate('common', "File %s does not exist."), $cmd);
			return $result;
		}
	}

	$cmd .= ' -version';
	fs_exec($cmd, $results, $status, $debugfile);

	if ($status != $gallery->app->expectedExecStatus) {
		$result['error'] = sprintf(gTranslate('common', "Expected status: %s, but actually received status %s."),
			$gallery->app->expectedExecStatus,
			$status);
		return $result;
	}

	/*
	* Windows does not appear to allow us to redirect STDERR output, which
	* means that we can't detect the version number.
	*/
	if (getOS() == OS_WINDOWS) {
		$result['warning'] = "<i>" . gTranslate('common', "can't detect version on Windows.") ."</i>";
	}
	else if (eregi("version: (.*) http(.*)$", $results[0], $regs)) {
		$version = $regs[1];
		$result['ok'] = sprintf(gTranslate('common', "OK!  Version: %s"), $version);
	}
	else {
		$result['error'] = $output[0];
	}

	return $result;
}

function checkNetPbm($cmd) {
	global $gallery;
	global $debugfile;

	$status = '';
	$cmd = fs_executable($gallery->app->pnmDir . "/$cmd");
	$result[] = fs_import_filename($cmd);
	$ok = true;

	if (inOpenBasedir($gallery->app->pnmDir)) {
		if (! fs_file_exists($cmd)) {
			$result['error'] = sprintf(gTranslate('common', "File %s does not exist."), $cmd);
			$ok = false;
		}
	}

	$cmd .= " --version";

	fs_exec($cmd, $results, $status, $debugfile);

	if ($status != $gallery->app->expectedExecStatus) {
		$result['error'] = sprintf(gTranslate('common', "Expected status: %s, but actually received status %s."),
			$gallery->app->expectedExecStatus,
			$status);

		$ok = false;
	}

	/*
	* Windows does not appear to allow us to redirect STDERR output, which
	* means that we can't detect the version number.
	*/
	if ($ok) {
		if (getOS() == OS_WINDOWS) {
			$result['warning'] = "<i>" . gTranslate('common', "can't detect version on Windows") ."</i>";
		} else {
			$output = array();
			if (file_exists($debugfile)) {
				if ($fd = fs_fopen($debugfile, "r")) {
					while (!feof($fd)) {
						$output[] = fgets($fd, 4096);
					}
					fclose($fd);
				}
				unlink($debugfile);
			}

			if (eregi("using lib(pbm|netpbm) from netpbm version: netpbm (.*)[\n\r]$",  $output[0], $regs)) {
				$version = $regs[2];
				$result['ok'] = sprintf(gTranslate('common', "OK!  Version: %s"), $version);
			} else {
				$result['error'] = $output[0];
				$ok = false;
			}
		}

	}
	else {
		$result['error'] = gTranslate('common', "Unknown error occured.");
	}

	return $result;
}

/**
 * returns the current graphicTool set in config.php
 * if not found, then the default is returned
 */
function getCurrentGraphicTool() {
	global $gallery;

	if(isset($gallery->app->graphics)) {
		return $gallery->app->graphics;
	}
	else {
		return default_graphics();
	}
}

function embed_hidden($key) {
	global $$key;

	$html = '';
	$real = $$key;

	if (is_array($real)) {
		foreach ($real as $real_key => $value) {
			if (is_array($value)) {
				foreach($value as $sub_key => $sub_value) {
					$name = stripWQuotesON($key . "[$real_key][$sub_key]");
					$html .= '<input type="hidden" name="'. $name .'" value="';
					$html .= urlencode($sub_value);
					$html .= "\">\n";
				}
			}
			else {
				$name = stripWQuotesON("$key" . "[$real_key]");
				$html .= '<input type="hidden" name="'. $name .'" value="';
				$html .= urlencode($value);
				$html .= "\">\n";
			}
		}
	}
	else {
		$html .= '<input type="hidden" name="'. stripWQuotesON($key) . '" value="';
		$html .= urlencode($real);
		$html .= "\">\n";
	}

	return $html;
}

function useSMTP() {
	global $gallery;

	if(isset($gallery->app->useOtherSMTP)) {
		return $gallery->app->useOtherSMTP;
	}
	else {
		return 'no';
	}
}

function configError($msg) {
	$html = '<div class="g-error" style="color: red;">' . $msg . '</div>';

	return $html;
}
?>
