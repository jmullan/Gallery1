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
 * $Id: filesystem.php 17882 2008-08-19 18:23:49Z JensT $
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

	$size = 0;
	if ($handle = fs_opendir($path)) {
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
	}

	return $size;
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

function findInPath($program) {
	$path = explode(':', getenv('PATH'));

	foreach ($path as $dir) {
		if (fs_file_exists("$dir/$program")) {
			return "$dir/$program";
		}
	}

	return false;
}

function parse_csv ($filename, $delimiter = ';') {
	echo debugMessage(sprintf(gTranslate('core', "Parsing for csv data in file: %s"), $filename), __FILE__, __LINE__);
	$maxLength = 1024;
	$return_array = array();

	if ($fd = fs_fopen($filename, "rt")) {
		$headers = fgetcsv($fd, $maxLength, $delimiter);
		while ($columns = fgetcsv($fd, $maxLength, $delimiter)) {
			$i = 0;
			$current_image = array();
			foreach ($columns as $column) {
				$current_image[$headers[$i++]] = $column;
			}
			$return_array[] = $current_image;
		}
		fclose($fd);
	}

	if(isDebugging()){
		echo gTranslate('core', "csv result:");
		print_r($return_array);
	}

	return $return_array;
}

/**
 * Extracts an archive file into a subfolder in the Gallery temp dir
 *
 * @param string $archive       Full path to the archive that you want to extract.
 * @param string $ext           Extension of the archive. We give it, as we extract tempfiles (.tmp) from Uploads.
 * @param string $destination   Foldername where the archive is extracted to. Be carefull!
 * @return boolean              True on succes, otherwise false.
 * @author Jens Tkotz
 * @Since 1.5.8
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

function getArchiveFileNames($archive, $ext) {
	global $gallery;

	$cmd = '';
	$files = array();

	if ($tool = canDecompressArchive($ext)) {
		$filename = fs_import_filename($archive);
		switch ($tool) {
			case 'zip':
				$cmd = fs_import_filename($gallery->app->zipinfo) ." -1 ". $filename;
				break;

			case 'rar':
				$cmd = fs_import_filename($gallery->app->rar) ." vb ". $filename;
				break;
		}

		list($files, $status) = exec_internal($cmd);

		if (!empty($files)) {
			sort($files);
		}
	}

	return $files;
}

/**
 * If an exiftool is installed then gallery tries to pull out EXIF Data.
 * Only fields with data are returned.
 */
function getExif($file) {
	global $gallery;

	$return = array();
	$myExif = array();
	$unwantedFields = array();

	echo debugMessage(sprintf(gTranslate('core', "Getting Exif from: %s"), $file), __FILE__, __LINE__, 3);

	switch(getExifDisplayTool()) {
		case 'exiftags':
			if (empty($gallery->app->exiftags)) {
				break;
			}

			$path	= $gallery->app->exiftags;
			$cmd	= fs_import_filename($path, 1) . ' -au';
			$target	= fs_import_filename($file, 1);

			list($return, $status) = @exec_internal($cmd . ' ' . $target);
		break;

		case 'jhead':
			if (empty($gallery->app->use_exif)) {
				break;
			}
			$path = $gallery->app->use_exif;
			list($return, $status) = @exec_internal(fs_import_filename($path, 1) .' ' . // -v removed as the structure is different.
			fs_import_filename($file, 1));

			$unwantedFields = array('File name');
		break;

		default:
			return array(false,'');
		break;
	}

	if ($status == 0) {
		foreach ($return as $value) {
			$value = trim($value);

			if (!empty($value)) {
				$explodeReturn = explode(':', $value, 2);
				$exifDesc = trim(htmlentities($explodeReturn[0]));
				$exifData = trim(htmlentities($explodeReturn[1]));

				if(!empty($exifData) &&
					!in_array($exifDesc, $unwantedFields) &&
					!isset($myExif[$exifDesc]))
				{
					if (isset($myExif[$exifDesc])) {
						$myExif[$exifDesc] .= "<br>";
					}
					else {
						$myExif[$exifDesc] = '';
					}

					$myExif[$exifDesc] .= trim($exifData);
				}
			}
		}
	}

	return array($status, $myExif);
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

function safe_serialize($obj, $file) {
	global $gallery;

	if (!strcmp($gallery->app->use_flock, "yes")) {
		/* Acquire an advisory lock */
		$lockfd = fs_fopen("$file.lock", "a+");
		if (!$lockfd) {
			echo gallery_error(sprintf(gTranslate('core', "Could not open lock file (%s) for writing!"),
						"$file.lock"));
			return 0;
		}
		if (!flock($lockfd, LOCK_EX)) {
			echo gallery_error(sprintf(gTranslate('core', "Could not acquire lock (%s)!"),
						"$file.lock"));
			return 0;
		}
	}

	/*
	 * Don't use tempnam because it may create a file on a different
	 * partition which would cause rename() to fail.  Instead, create our own
	 * temporary file.
	 */
	$i = 0;
	do {
		$tmpfile = "$file.$i";
		$i++;
	} while (fs_file_exists($tmpfile));

	if ($fd = fs_fopen($tmpfile, "wb")) {
		$buf = serialize($obj);
		$bufsize = strlen($buf);
		$count = fwrite($fd, $buf);
		fclose($fd);

		if ($count != $bufsize || fs_filesize($tmpfile) != $bufsize) {
			/* Something went wrong! */
			$success = 0;
		}
		else {
			/*
			 * Make the current copy the backup, and then
			 * write the new current copy.  There's a
			 * potential race condition here if the
			 * advisory lock (above) fails; two processes
			 * may try to do the initial rename() at the
			 * same time.  In that case the initial rename
			 * will fail, but we'll ignore that.  The
			 * second rename() will always go through (and
			 * the second process's changes will probably
			 * overwrite the first process's changes).
			 */
			if (fs_file_exists($file)) {
				fs_rename($file, "$file.bak");
			}
			fs_rename($tmpfile, $file);
			$success = 1;
		}
	}
	else {
		$success = 0;
	}

	if (!strcmp($gallery->app->use_flock, "yes")) {
		flock($lockfd, LOCK_UN);
	}
	return $success;
}

function unsafe_serialize($obj, $file) {
	/*
	 * Don't use tempnam because it may create a file on a different
	 * partition which would cause rename() to fail.  Instead, create our own
	 * temporary file.
	 */
	$i = 0;
	do {
		$tmpfile = "$file.$i";
		$i++;
	} while (fs_file_exists($tmpfile));

	if ($fd = fs_fopen($tmpfile, "wb")) {
		$buf = serialize($obj);
		$bufsize = strlen($buf);
		$count = fwrite($fd, $buf);
		fclose($fd);

		if ($count != $bufsize || fs_filesize($tmpfile) != $bufsize) {
			/* Something went wrong! */
			$success = false;
		}
		else {
			/*
			 * Make the current copy the backup, and then write the new current copy.
			 * There's a potential race condition here.
			 * Two processes may try to do the initial rename() at the
			 * same time.  In that case the initial rename will fail,
			 * but we'll ignore that.  The second rename() will always go through
			 * (and the second process's changes will probably
			 * overwrite the first process's changes).
			 */
			if (fs_file_exists($file)) {
				fs_rename($file, "$file.bak");
			}
			fs_rename($tmpfile, $file);
			$success = true;
		}
	}
	else {
		$success = false;
	}

	return $success;
}

?>
