<?
if (substr(PHP_OS, 0, 3) == 'WIN') {
	require("../platform/fs_win32.php");
	if (fs_file_exists("SECURE")) {
?>
Gallery is in secure mode and cannot be configured.
If you want to configure it, you must run the <b>configure.bat</b>
script in the gallery directory then reload this page.
<?
		exit;
	}
} else {
	require("../platform/fs_unix.php");
}

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