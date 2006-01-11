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
 * @package Filesystem_unix
 */

/**
 * Copies a file from $source to $dest.
 * @param  string    $source    Full path to source file.
 * @param  string    $dest      Full path to destination file.
 * @return boolean   $result    true on success, otherwise false
 */
function fs_copy($source, $dest) {
    $result = copy($source, $dest);
    chmod ($dest, 0644);

    return $result;
}

function fs_exec($cmd, &$results, &$status, $debugfile="") {
	if (!empty($debugfile)) {
		$cmd = "($cmd) 2>$debugfile";
	} 
	return exec($cmd, $results, $status);
}

function fs_tempdir() {
	return export_filename(getenv("TEMP"));
}

function fs_file_exists($filename) {
	return @file_exists($filename);
}

function fs_is_link($filename) {
	/* if the link is broken it will spew a warning, so ignore it */
	return @is_link($filename);
}

function fs_filesize($filename) {
	return filesize($filename);
}

function fs_fopen($filename, $mode, $use_include_path=0) {
	return fopen($filename, $mode, $use_include_path);
}

function fs_file_get_contents($filename) {
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
	return @is_dir($filename);
}

function fs_is_file($filename) {
	return @is_file($filename);
}

function fs_is_readable($filename) {
	return @is_readable($filename);
}

function fs_is_writable($filename) {
	return @is_writable($filename);
}

function fs_opendir($path) {
    $dir_handle = @opendir($path);
    if ($dir_handle) {
	return $dir_handle;
    }
    else {
	echo "\<br>". gallery_error(sprintf(_("Gallery was not able to open dir: %s. <br>Please check permissions and existence"), $path));
	return false;
    }
}

function fs_rename($oldname, $newname) {
	return rename($oldname, $newname);
}

function fs_stat($filename) {
	return stat($filename);
}

/* This function deletes a file.
** The errormessage is surpressed !
*/
function fs_unlink($filename) {
	return @unlink($filename);
}

function fs_is_executable($filename) {
	return is_executable($filename);
}

function fs_import_filename($filename, $for_exec = true) {
    if ($for_exec) {
	$filename = escapeshellarg($filename); // Might as well use the function PHP provides!
    }
	
    return $filename;
}

function fs_export_filename($filename) {
	return $filename;
}

function fs_executable($filename) {
	return $filename;
}

/**
 * Creates a directory
 * @param  string    $dirname
 * @param  string    $perms     Optional perms, given in octal format
 * @return boolean   $result    true on success, otherwise false
 */
function fs_mkdir($dirname, $perms = 0700) {
    /*
     * PHP 4.2.0 on Unix (specifically FreeBSD) has a bug where mkdir
     * causes a seg fault if you specify modes.
     *
     * See: http://bugs.php.net/bug.php?id=16905
     *
     * We can't reliably determine the OS, so let's just turn off the
     * permissions for any Unix implementation.
     */
    if ( phpversion() == '4.2.0') {
	   $result = mkdir(fs_import_filename($dirname, 0));
       chmod($dirname, $perms);
    } else {
	   $result = mkdir(fs_import_filename($dirname, 0), $perms);
    }

    return $result;
}
?>
