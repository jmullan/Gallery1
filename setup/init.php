<?
/*
 * Init prepend file for setup directory.
 */

$GALLERY_DIR = dirname(dirname(getenv("SCRIPT_FILENAME")));
$GALLERY_URL = dirname(dirname(getenv("SCRIPT_NAME")));
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