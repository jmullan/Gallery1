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
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
  <title><?php echo _("Resize Photo") ?></title>
  <?php echo getStyleSheetLink() ?>


</head>
<body dir="<?php echo $gallery->direction ?>">
<span class="popup">
<?php
$all = !strcmp($index, "all");
if ($gallery->session->albumName && isset($index)) {
	if (isset($manual) && $manual >0) $resize=$manual;
	if (! empty($resize)) {
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
<font size="+1"><?php echo _("Resizing photos") ?></font>
<br>
<?php echo _("This will resize your photos so that the longest side of the photo is equal to the target size below and the filesize will be close to the chosen size. ") ?>

<?php echo $all ? _("What is the target size for all the photos in this album?") : _("What is the target size for this photo?") ?>
<p>
<?php echo makeFormIntro("resize_photo.php"); ?>
<p>
<?php print _("Target filesize") ?> 
<input type="text" size="4" name="resize_file_size" value="<?php print $gallery->album->fields["resize_file_size"] ?>" >  kbytes
<p>
<?php print _("Maximum side length in pixels") ?> 
<p>

<input type="hidden" name="index" value="<?php echo $index ?>">
	<input type="submit" name="resize" value="<?php echo _("Get rid of resized") ?>">
		<?php echo _("(Use only the original picture)"); ?>

<p>
<table border="0">
<?php 
	$choices=array(1280,1024,700,800,640,600,500,400);
	for ($i=0; $i<count($choices); $i=$i+2) {
		echo "\n<tr>";
		echo "\n\t". '<td><input type="radio" name="resize" value="' . $choices[$i] .'" id="size_' . $choices[$i] .'">'. '<label for="size_' . $choices[$i] .'">'. $choices[$i] .'</label></td>';
		echo "\n\t". '<td><input type="radio" name="resize" value="' .$choices[$i+1].'" id="size_' .$choices[$i+1].'">'. '<label for="size_' .$choices[$i+1].'">'.$choices[$i+1].'</label></td>';
		echo "\n</tr>";
	}
?>

<tr>
	<td colspan="2"><input id="none" type="radio" name="resize" value="manual">
		<input type="text" size="5" name="manual" onFocus="document.getElementById('none').checked=true;">
			<label for="none"> <?php echo _("(manual value)"); ?></lablel></td>
</tr>
</table>
</p>

<input type="submit" name="change_size" value="<?php echo _("Change Size") ?>">
<input type="button" value="<?php echo _("Cancel") ?>" onclick="parent.close()">
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

</center>

</span>
</body>
</html>
