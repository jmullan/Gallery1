<?
/*
 * Init prepend file for setup directory.
 */

$GALLERY_DIR = dirname(dirname($HTTP_SERVER_VARS["PATH_TRANSLATED"]));
$GALLERY_URL = dirname(dirname($HTTP_SERVER_VARS["PHP_SELF"]));

// Make sure GALLERY_URL doesn't end in a slash
$GALLERY_URL = preg_replace("/\/$/", "", $GALLERY_URL);

$MIN_PHP_MAJOR_VERSION = 4;

if ($init_mod_rewrite) {
	$GALLERY_REWRITE = 1;
	if (strstr($init_mod_rewrite, "ampersandbroken")) {
		$GALLERY_REWRITE_SEPARATOR = "\&";
	} else {
		$GALLERY_REWRITE_SEPARATOR = "&";
	}
} else {
	$GALLERY_REWRITE = 0;
}

?>