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
function getExtension($filename, $withDebug = true) {
	$ext = ereg_replace(".*\.([^\.]*)$", "\\1", $filename);
	$ext = strtolower($ext);

	if($withDebug) {
		echo debugMessage(sprintf(gTranslate('core', "extension of file %s is %s"), basename($filename), $ext), __FILE__, __LINE__, 3);
	}

	return $ext;
}

/**
 * Remove a directory and its complete content.
 *
 * @param string $dir
 * @return boolean 		True on success
 */
function rmdirRecursive($dir) {
	if($objs = glob($dir."/*")){
		foreach($objs as $obj) {
			if(is_dir($obj)) {
				rmdirRecursive($obj);
			}
			else {
				fs_unlink($obj);
			}
		}
	}

	return fs_rmdir($dir);
}

/**
 * Extracts an archive file into a subfolder in the Gallery temp dir
 *
 * @param string $archive       Full path to the archive that you want to extract.
 * @param string $ext           Extension of the archive. We give it, as we extract tempfiles (.tmp) from Uploads.
 * @param string $destination   Foldername where the archive is extracted to. Be carefull!
 * @return boolean              True on succes, otherwise false.
 * @author Jens Tkotz
 * @Since 1.6
 */
function extractArchive($archive, $ext, $destination) {
	global $gallery;

	if(! fs_is_dir($destination) && !fs_mkdir($destination)) {
		echo debugMessage(sprintf(gTranslate('core', "Extracting: %s failed. Not able create the folder to extract the archive in."), $archive),__FILE__, __LINE__);
		return  false;
	}

	$archiveName = fs_import_filename($archive);
	$destination = fs_import_filename($destination);

	if($tool = canDecompressArchive($ext)) {
		echo debugMessage(sprintf(gTranslate('core', "Extracting: %s with %s"), $archive, $tool),__FILE__, __LINE__,3);

		switch($tool) {
			case 'zip':
				$unzip	= fs_import_filename($gallery->app->unzip);
				$cmd	= "$unzip -j -o $archiveName -d $destination";
				break;

			case 'rar':
				$rar	= fs_import_filename($gallery->app->rar);
				$cmd	= "$rar e $archiveName $destination";
				break;
		}

		return exec_wrapper($cmd);
	}
	else {
		echo debugMessage(sprintf(gTranslate('core', "%s with extension %s is not an supported archive."), $archive, $ext),__FILE__, __LINE__);
		return false;
	}
}

/**
 * Checks wether an url i in the allowed upload pathes of Gallery.
 *
 * @param string $url
 * @return arrray($ret, $msg)	$ret is true on success, otherwise false. $msg contains the errormsg
 * @author Jens Tkotz
 */
function isInAllowedUploadPath($url) {
	global $gallery;

	$msg = '';
	$ret = false;

	if(empty($gallery->app->uploadPaths)) {
		$ret = false;
		$msg = infobox(array(array(
				'type' => 'error',
				'text' => gTranslate('core', "You are not allowed to upload from local directories.")
		)));

		return array($ret, $msg);
	}

	foreach ($gallery->app->uploadPaths as $path) {
		if (strpos($url, $path) === 0 && isXSSclean($url, 0)) {
			$inAllowedPath = true;
		}
	}

	if(! isset($inAllowedPath)) {
		$ret = false;
		$msg = infobox(array(array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "%s is not in the list of allowed uploadpathes. Skipping."),
						'<i>' . htmlspecialchars(strip_tags(urldecode($url))) . '</i>')
		)));
		return array($ret, $msg);
	}

	return array(true, '');
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
