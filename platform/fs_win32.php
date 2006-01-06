<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * @package Filesystem_windows
 */

/**
 * Copies a file from $source to $dest.
 * @param  string    $source    Full path to source file.
 * @param  string    $dest      Full path to destination file.
 * @return boolean   $result    true on success, otherwise false
 */
function fs_copy($source, $dest) {
    $result = copy(fs_import_filename($source, 0), fs_import_filename($dest, 0));
    chmod (fs_import_filename($dest, 0), 0644);

    return $result;	
}

function fs_file_exists($filename) {
	$filename = fs_import_filename($filename, 0);
	debug("Checking for [$filename] == " . @file_exists($filename));
	return @file_exists($filename);
}

function fs_is_link($filename) {
	$filename = fs_import_filename($filename, 0);
	return is_link($filename);
}

function fs_filesize($filename) {
	$filename = fs_import_filename($filename, 0);
	return filesize($filename);
}

function fs_fopen($filename, $mode, $use_include_path=0) {
	$filename = fs_import_filename($filename, 0);
	return fopen($filename, $mode, $use_include_path);
}

function fs_file_get_contents($filename) {
	$filename = fs_import_filename($filename, 0);

	if (function_exists("file_get_contents")) {
		$tmp = @file_get_contents($filename);
	} else {
		if ($fd = fs_fopen($fname, "rb")) {
			while (!feof($fd)) {
				$tmp .= fread($fd, 65536);
			}
			fclose($fd);
		}
	}
	return $tmp;
}

function fs_is_dir($filename) {
	$filename = fs_import_filename($filename, 0);
	return @is_dir($filename);
}

function fs_is_file($filename) {
	$filename = fs_import_filename($filename, 0);
	return @is_file($filename);
}

function fs_is_readable($filename) {
	$filename = fs_import_filename($filename, 0);
	return @is_readable($filename);
}

function fs_is_writable($filename) {
	$filename = fs_import_filename($filename, 0);
        return @is_writable($filename);
}

function fs_opendir($path) {
    $path = fs_import_filename($path, 0);

    $dir_handle = @opendir($path);
    if ($dir_handle) {
        return $dir_handle;
    }
    else {
        echo gallery_error(sprintf(_("Gallery was not able to open dir: %s. <br>Please check permissions and existence"), $path));
	return false;
    }
}

function fs_rename($oldname, $newname) {
	$oldname = fs_import_filename($oldname, 0);
	$newname = fs_import_filename($newname, 0);

	/* 
	 * It appears that win32 doesn't like it when you rename 
	 * a file to end with ".dat.bak".  Why?  This is very 
	 * annoying.
	 */
	$newname = str_replace(".dat.bak", ".bak", $newname);

	debug("Rename $oldname -> $newname");
	clearstatcache();
	if (file_exists("$newname.bak")) {
		unlink("$newname.bak");
	}
	if (file_exists("$newname")) {
		return rename($newname, "$newname.bak") &&
			rename($oldname, $newname);
	} else {
		return rename($oldname, $newname);
	}
}

function fs_stat($filename) {
	$filename = fs_import_filename($filename, 0);
	return stat($filename);
}

/* This function deletes a file.
** The errormessage is surpressed !
*/
function fs_unlink($filename) {
	$filename = fs_import_filename($filename, 0);
	return @unlink($filename);
}

function fs_executable($filename) {
	$filename = fs_import_filename($filename, 0);
	if (!strstr($filename, ".exe")) {
		$filename .= ".exe";
	}
	return $filename;
}

/**
 * Creates a directory
 * @param  string    $dirname
 * @param  string    $perms     Optional perms, given in octal format
 * @return boolean   $result    true on success, otherwise false
 */
function fs_mkdir($dirname, $perms = 0700) {
    $result = mkdir(fs_import_filename($dirname, 0), $perms);

    return $result;
}

function fs_import_filename($filename, $for_exec=1) {
	debug("Import before: $filename");
	# Change / and : to \ and ;
	#
 	$filename = str_replace("/", "\\", $filename);
 	$filename = str_replace(":", ";", $filename);

	# Change D;\apps to D:\apps (the : got mangled by the above
	# transform).
	#
	if ($filename{1} == ';') {
		$filename{1} = ':';
	}

	# Convert "D\whoami" to "D:\whoami"
	#
	$filename = ereg_replace("^([A-Z])\\\\(.*)", "\\1:\\\\2", $filename);

	# Convert "\Perl\bin\;D/whoami" to "D:\Perl\bin\whoami"
	#
	$filename = ereg_replace("(.*);([A-Z])\\\\(.*)", "\\2:\\1\\3", $filename);

	if ($for_exec) {
		if (strstr($filename, " ")) {
			$filename = "\"$filename\"";
		}
	}	

	debug("Import after: $filename");
	return $filename;
}

function fs_export_filename($filename) {
	
	# Convert "d:\winnt\temp" to "d:/winnt/temp"
	#
	while (strstr($filename, "\\\\")) {
		$filename = str_replace("\\\\", "\\", $filename);
	}
	$filename = str_replace("\\", "/", $filename);

	return $filename;
}

function fs_exec($cmd, &$results, &$status, $debugfile) {

	// We can't redirect stderr with Windows.  Hope that we won't need to.
	return exec($cmd, $results, $status);
}

function fs_tempdir() {
	return export_filename($gallery->app->tmpDir); 
}

function fs_is_executable($filename) {
	return eregi(".(exe|com|vbs)$", $filename);
}

function debug($msg) {
	if (0) {
		print "<br>$msg<br>";
	}
}
?>
