<? 

/*
 * Protect against very old versions of 4.0 (like 4.0RC1) which 
 * don't implicitly create a new stdClass() when you use a variable
 * like a class.
 */
if (!$gallery) {
	$gallery = new stdClass();
}

$gallery->version = "1.2-cvs-b8";
$gallery->config_version = 23;
$gallery->album_version = 3;
$gallery->url = "http://gallery.sourceforge.net";
?>
