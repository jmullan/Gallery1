<?php /* $Id$ */ ?>
<?php
/*
 * Turn down the error reporting to just critical errors for now.
 * In v1.2, we know that we'll have lots and lots of warnings if
 * error reporting is turned all the way up.  We'll fix this in v2.0
 */
error_reporting(E_ALL & ~E_NOTICE);

/* emulate part of register_globals = on */
/*
 * Prevent hackers from overwriting one HTTP_ global using another one.  For example,
 * appending "?HTTP_POST_VARS[GALLERY_BASEDIR]=xxx" to the url would cause extract
 * to overwrite HTTP_POST_VARS when it extracts HTTP_GET_VARS
 */
$scrubList = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_POST_FILES');
foreach ($scrubList as $outer) {
    foreach ($scrubList as $inner) {
	unset(${$outer}[$inner]);
    }
}
extract($HTTP_GET_VARS);
extract($HTTP_POST_VARS);
extract($HTTP_COOKIE_VARS);

if (getOS() == OS_WINDOWS) {
	include("../platform/fs_win32.php");
	if (fs_file_exists("SECURE")) {
		echo "Gallery is in secure mode and cannot be configured. If you want to configure it, you must run the <b>configure.bat</b> script in the gallery directory then reload this page.";
		exit;
	}
} else {
	include("../platform/fs_unix.php");
}

/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

/*
 * Init prepend file for setup directory.
 */
$GALLERY_DIR = dirname(dirname(realpath(__FILE__)));
if (!strcmp($GALLERY_DIR, ".") || !strcmp($GALLERY_DIR, "/")) {
    $tmp = $HTTP_SERVER_VARS["PATH_TRANSLATED"];
    if (!$tmp) {
	$tmp = $HTTP_ENV_VARS["PATH_TRANSLATED"];
    }
    if (!$tmp) {
	$tmp = getenv("SCRIPT_FILENAME");
    }
    $GALLERY_DIR = dirname(dirname($tmp));
}

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
