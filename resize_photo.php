<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>

<html>
<head>
  <title><?php echo _("Resize Photo") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<?php
$all = !strcmp($index, "all");
if ($gallery->session->albumName && isset($index)) {
	if ($resize) {
		if (!strcmp($index, "all")) {
			$np = $gallery->album->numPhotos(1);
			echo("<br> ". sprintf(_("Resizing %d photos..."),$np));
			my_flush();
			for ($i = 1; $i <= $np; $i++) {
				echo("<br> ". _("Processing image") . " $i...");
				my_flush();
				set_time_limit($gallery->app->timeLimit);
				$gallery->album->resizePhoto($i, $resize, $resize_file_size);
			}
		} else {
			echo("<br> ". _("Resizing 1 photo..."));
			my_flush();
			set_time_limit($gallery->app->timeLimit);
			$gallery->album->resizePhoto($index, $resize, $resize_file_size);
		}
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
<font size=+1><?php echo _("Resizing photos") ?></a>
<br>
<?php echo _("This will resize your photos so that the longest side of the photo is equal to the target size below and the filesize will be close to the chosen size. ") ?>

<?php echo $all ? _("What is the target size for all the photos in this album?") : _("What is the target size for this photo?") ?>
<p>
<?php echo makeFormIntro("resize_photo.php"); ?>
<p>
<?php print _("Target filesize") ?> 
<input type="text" size=4 name="resize_file_size" value="<?php print $gallery->album->fields["resize_file_size"] ?>" >  kbytes
<p>
<?php print _("Maximum side length in pixels") ?> 
<p>
<input type=hidden name=index value=<?php echo $index ?>>
<input type=submit name=resize value="<?php echo _("Original Size") ?>">
<input type=submit name=resize value="1024">
<input type=submit name=resize value="800">
<input type=submit name=resize value="700">
<input type=submit name=resize value="640">
<input type=submit name=resize value="600">
<input type=submit name=resize value="500">
<input type=submit name=resize value="400">
<br>
<input type=submit value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<br><br>
<?php
if (!$all) {
	echo $gallery->album->getThumbnailTag($index);
} 
?>

<?php
	}
} else {
	gallery_error(_("no album / index specified"));
}
?>

</body>
</html>





