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

/**
 * @package 	Libs
 * @subpackage	Filesystem
 * @uses	Messages, Content, Lang
 */

/**
 * Returns the diskspace in bytes a user consumes.
 * To make it not too slow, not every element is counted. Its done per album
 * So even if elements do not belong to the album owner, they are counted.
 *
 * @param int		$uid
 * @param int		$warning_size	If diskusage is higher then this, the output gets a differnt css
 * @return array			($formattedResult, $bytes);
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
 * @param string	$path		Absolute path to a local directory
 * @param boolean	$recursive
 * @return int		$size		Size in bytes
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
 *
 * @param  string   $filename
 * @return string   $ext
 * @uses   lib/messages
 * @author Jens Tkotz
 */
function getExtension($filename) {
	$ext = ereg_replace(".*\.([^\.]*)$", "\\1", $filename);
	$ext = strtolower($ext);

	echo debugMessage(sprintf(gTranslate('core', "extension of file %s is %s"), basename($filename), $ext), __FILE__, __LINE__, 3);
	return $ext;
}

/**
 * Remove a directory and its complete content.
 *
 * @param string $dir
 */
function rmdirRecursive($dir) {
	if($objs = glob($dir."/*")){
		foreach($objs as $obj) {
			if(is_dir($obj)) {
				rmdirRecursive($obj);
			}
			else {
				unlink($obj);
			}
		}
	}

	rmdir($dir);
}

/**
 * Checks whether the given path or the given path with .default extension exists.
 * Function exits Galelry with an error message when a path does not exist at all.
 *
 * @param	string $localPath		Path to a file on the server.
 * @return	string				The path that exist.
 * @author	Jens Tkotz
 */
function getDefaultFilename($localPath) {
	if(fs_file_exists($localPath)) {
		return $localPath;
	}
	elseif (fs_file_exists($localPath . '.default')) {
		return $localPath . '.default';
	}
	else {
		echo gallery_error(gTranslate('common', "The path you try to use does not exist! Exiting Gallery"));
		exit;
	}
}
?>
