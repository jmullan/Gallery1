<?
/* 
 * Turn off magic quotes runtime as they interfere with saving and
 * restoring data from our file-based database files
 */
set_magic_quotes_runtime(0);

/*
 * Init prepend file for setup directory.
 */
$tmp = $HTTP_SERVER_VARS["PATH_TRANSLATED"];
if (!$tmp) {
	$tmp = $HTTP_ENV_VARS["PATH_TRANSLATED"];
}
if (!$tmp) {
	$tmp = getenv("SCRIPT_FILENAME");
}
$GALLERY_DIR = dirname(dirname($tmp));

$tmp = $HTTP_SERVER_VARS["PHP_SELF"];
if (!$tmp) {
	$tmp = $HTTP_ENV_VARS["PHP_SELF"];
}
if (!$tmp) {
	$tmp = getenv("SCRIPT_NAME");
}

$GALLERY_URL = dirname(dirname($tmp));

// Make sure GALLERY_URL doesn't end in a slash
$GALLERY_URL = preg_replace("/\/$/", "", $GALLERY_URL);

$MIN_PHP_MAJOR_VERSION = 4;

if ($init_mod_rewrite) {
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