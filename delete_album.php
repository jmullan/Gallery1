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
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack check
if (!$gallery->user->canDeleteAlbum($gallery->album)) {
	exit;
}

if ($confirm) {
	$gallery->album->delete();
	dismissAndReload();
	return;
}

if ($gallery->album) {
?>

<html>
<head>
  <title>Delete Album</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead">Delete Album</span>
<br><br>
Do you really want to delete this album?
<br>
<b><?php echo $gallery->album->fields["title"] ?></b>
<p>
<?php echo makeFormIntro("delete_album.php"); ?>
<input type=submit name=confirm value="Delete">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<p>
<?php
	if ($gallery->album->numPhotos(1)) {
		echo $gallery->album->getThumbnailTag($gallery->album->getHighlight());
	}
} else {
	gallery_error("no album specified");
}
?>

</body>
</html>
