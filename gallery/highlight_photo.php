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

<html>
<head>
  <title><?php echo _("Highlight Photo") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<?php
if ($gallery->session->albumName && isset($index)) {
	if ($confirm) {
		$gallery->album->setHighlight($index);
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
<span class="popup">
<?php echo _("Do you want this photo to be the one that shows up on the gallery page, representing this album?") ?>
<br>
<br>

<?php echo $gallery->album->getThumbnailTag($index) ?>
<br>
<?php echo $gallery->album->getCaption($index) ?>
<br>
<?php echo makeFormIntro("highlight_photo.php"); ?>
<input type="hidden" name="index" value="<?php echo $index ?>">
<input type="submit" name="confirm" value="<?php echo _("Yes") ?>">
<input type="button" name ="no" value="<?php echo _("No") ?>" onclick='parent.close()'>
</form>

<?php
	}
} else {
	gallery_error("no album / index specified");
}
?>

</span>
</body>
</html>

