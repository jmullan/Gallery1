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
/*
 * Turn down the error reporting to just critical errors for now.
 * In v1.2, we know that we'll have lots and lots of warnings if
 * error reporting is turned all the way up.  We'll fix this in v2.0
 */


if (isset($gallery->app->devMode) && $gallery->app->devMode == "yes") {
	error_reporting(E_ALL);
} else {
	error_reporting(E_ALL & ~E_NOTICE);
}

/*
 * Figure out if register_globals is on or off and save that info
 * for later
 */
$register_globals = ini_get("register_globals");
if (empty($register_globals) ||
	!strcasecmp($register_globals, "off") ||
	!strcasecmp($register_globals, "false")) {
    $gallery->register_globals = 0;
} else {
    $gallery->register_globals = 1;
}

/*
 * If register_globals is off, then extract all HTTP variables into the global
 * namespace.  
 */
if (!$gallery->register_globals) {

    /*
     * Prevent hackers from overwriting one HTTP_ global using another one.  For example,
     * appending "?HTTP_POST_VARS[gallery]=xxx" to the url would cause extract
     * to overwrite HTTP_POST_VARS when it extracts HTTP_GET_VARS
     */
    $scrubList = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_POST_FILES');
    if (function_exists("version_compare") && version_compare(phpversion(), "4.1.0", ">=")) {
	array_push($scrubList, "_GET", "_POST", "_COOKIE", "_FILES", "_REQUEST");
    }

    foreach ($scrubList as $outer) {
	foreach ($scrubList as $inner) {
	    unset(${$outer}[$inner]);
	}
    }
    
    if (is_array($_REQUEST)) {
	extract($_REQUEST);
    }
    else {
	if (is_array($HTTP_GET_VARS)) {
	    extract($HTTP_GET_VARS);
	}

	if (is_array($HTTP_POST_VARS)) {
	    extract($HTTP_POST_VARS);
	}

	if (is_array($HTTP_COOKIE_VARS)) {
	    extract($HTTP_COOKIE_VARS);
	}
    }


    if (is_array($HTTP_POST_FILES)) {
	foreach($HTTP_POST_FILES as $key => $value) {
	    ${$key."_name"} = $value["name"];
	    ${$key."_size"} = $value["size"];
	    ${$key."_type"} = $value["type"];
	    ${$key} = $value["tmp_name"];
	}
    }
    elseif (is_array($_FILES)) {
	foreach($_FILES as $key => $value) {
	    ${$key."_name"} = $value["name"];
	    ${$key."_size"} = $value["size"];
	    ${$key."_type"} = $value["type"];
	    ${$key} = $value["tmp_name"];
	}
    }
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

$tmp = $HTTP_SERVER_VARS["PHP_SELF"];
if (!$tmp) {
	$tmp = $HTTP_ENV_VARS["PHP_SELF"];
}
if (!$tmp) {
	$tmp = getenv("SCRIPT_NAME");
}

$GALLERY_URL = dirname(dirname($tmp));
// Make sure GALLERY_URL doesn't end in a slash
$GALLERY_URL = ereg_replace("\/$", "", $GALLERY_URL);

$MIN_PHP_MAJOR_VERSION = 4;

if (!empty($init_mod_rewrite)) {
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
