<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
if (isset($gallery->app->devMode) && $gallery->app->devMode == "yes") {
	error_reporting(E_ALL);
} else {
	error_reporting(E_ALL & ~E_NOTICE);
}

/* load necessary functions */
if (stristr (__FILE__, '/var/lib/gallery/setup')) {
	/* Gallery runs on a Debian System */
	require ('/usr/share/gallery/util.php');
} else {
	require (dirname(dirname(__FILE__)) . '/util.php');
}


/* define the constants */
getGalleryPaths();

if (getOS() == OS_WINDOWS) {
	require(GALLERY_BASE . '/platform/fs_win32.php');
} else {
	require(GALLERY_BASE . '/platform/fs_unix.php');
}
      
	@include (GALLERY_BASE . '/config.php');
	require (GALLERY_BASE . '/Version.php');
	require(GALLERY_BASE . "/session.php");

/* Set Language etc. */
	initLanguage();

/* We do this to get the config stylesheet */
	$GALLERY_OK=false;

/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

/*
 * Init prepend file for setup directory.
 */

$tmp = $_SERVER["PHP_SELF"];
if (!$tmp) {
	$tmp = $_ENV["PHP_SELF"];
}
if (!$tmp) {
	$tmp = getenv("SCRIPT_NAME");
}

$GALLERY_URL = dirname(dirname($tmp));
// Make sure GALLERY_URL doesn't end in a slash
$GALLERY_URL = ereg_replace("\/$", "", $GALLERY_URL);

$MIN_PHP_MAJOR_VERSION = 4;

if ($init_mod_rewrite = getRequestVar('init_mod_rewrite')) {
	$GALLERY_REWRITE_OK = 1;
	if (strstr($init_mod_rewrite, "ampersandbroken")) {
		$GALLERY_REWRITE_SEPARATOR = "\&";
	} else {
		$GALLERY_REWRITE_SEPARATOR = "&";
	}
} else {
	$GALLERY_REWRITE_OK = 0;
}

?>
