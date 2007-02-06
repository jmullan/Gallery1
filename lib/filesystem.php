<?php
/*
* Gallery - a web based photo album viewer and editor
* Copyright (C) 2000-2007 Bharat Mediratta
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
?>
<?php
/**
 * @package Libs
 */

/**
 * Returns the diskspace in bytes a user consumes.
 * To make it not too slow, not every element is counted. Its done per album
 * So even if elements do not belong to the album owner, they are counted.
 *
 * @param int		$uid
 * @param int		$warning_size		If diskusage is higher then this, the output gets a differnt css
 * @return array($formattedResult, $bytes);
 */
function usrDiskUsage($uid, $warning_size = 0) {
	global $gallery;

	$bytes = 0;
	$albumDB = new AlbumDB();

	foreach ($albumDB->albumList as $album) {
		if ($album->isOwner($uid)) {
			$albumPath = $gallery->app->albumDir."/".$album->fields["name"];
			$bytes += get_size($albumPath);
		}
	}

	if ($warning_size > 0 && $bytes > $warning_size) {
		$class = "g-error";
	}
	else {
		$class = "g-success";
	}

	$formattedResult = formatted_filesize($bytes);
	$formattedResult = "<span class=\"$class\">$formattedResult</span>";

	return array($formattedResult, $bytes);
}

/**
 * Returns the size ins bytes of a directory
 *
 * @param string	$path		Absolute path
 * @param boolean	$recursive
 * @return int		$size
 */
function get_size($path, $recursive = false) {
	if(!fs_is_dir($path)) {
		return filesize($path);
	}

	if ($handle = fs_opendir($path)) {
		$size = 0;
		while (false !== ($file = readdir($handle))) {
			if($file!='.' && $file!='..') {
				if(fs_is_dir($path.'/'.$file)) {
					if($recursive) {
						$size += get_size($path.'/'.$file);
					}
				}
				else {
					$size += fs_filesize($path.'/'.$file);
				}
			}
		}
		closedir($handle);
		return $size;
	}
}

/**
 * Extracts the extension of a given filename and returns it in lower chars.
 * @param  string   $filename
 * @return string   $ext
 * @author Jens Tkotz
 */
function getExtension($filename) {
	$ext = ereg_replace(".*\.([^\.]*)$", "\\1", $filename);
	$ext = strtolower($ext);

	echo debugMessage(sprintf(gTranslate('core', "extension of file %s is %s"), basename($filename), $ext), __FILE__, __LINE__, 3);
	return $ext;
}
?>
