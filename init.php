<?
/* Load defaults */
require('config.php');
require('classes.php');
require('util.php');
require('session.php');

/* Load the correct album object */
$album = new Album;
if ($albumName) {
	$album->load($albumName);
}
?>
