<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->canDeleteAlbum($gallery->album)) {
	exit;
}

if (!empty($delete)) {
	$gallery->album->delete();
	dismissAndReload();
	return;
}

if ($gallery->album) {
?>

<html>
<head>
  <title><?php echo _("Delete Album") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead"><?php echo _("Delete Album") ?></span>
<br><br>
<span class="popup">
<?php echo _("Do you really want to delete this album?") ?>
<br>
<b><?php echo $gallery->album->fields["title"] ?></b>
<p>
<?php echo makeFormIntro("delete_album.php"); ?>
<input type="submit" name="delete" value="<?php echo _("Delete") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<p>
<?php
	if ($gallery->album->numPhotos(1)) {
		echo $gallery->album->getHighlightTag();
	}
} else {
	gallery_error(_("no album specified"));
}
?>

</span>
</body>
</html>
