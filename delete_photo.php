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
	print _("Security violation"). "\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php

if (isset($id)) {
        $index = $gallery->album->getPhotoIndex($id);
}

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) 
	&& (!$gallery->album->getItemOwnerDelete()
	|| !$gallery->album->isItemOwner($gallery->user->getUid(), $index))) {
	exit;
}

if ($confirm && isset($id)) {
	if ($albumDelete) {
		/* Track down the corresponding photo index and remove it */
		$index = 0;
		for ($i = 1; $i <= sizeof($gallery->album->photos); $i++) {
		    $photo = $gallery->album->getPhoto($i);
		    if (isset($photo->isAlbumName) && !strcmp($photo->isAlbumName, $id)) {
			/* Found it */
			$index = $i;
			break;
		    }
		}
	}

	$gallery->album->deletePhoto($index);
	$gallery->album->save();
	if (isset($id2) && strlen($id2) > 0 && $id2 = $gallery->album->getPhotoId($id2)) {
	    dismissAndLoad(makeAlbumUrl($gallery->session->albumName, $id2));
	} else {
		dismissAndReload();
	}
	return;
}
?>

<html>
<head>
  <title><?php echo _("Delete Photo") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">


<?php
if ($gallery->album && isset($id)) {
	if (isset($albumDelete)) {
?>

<center>
<span class="popuphead"><?php echo _("Delete Album") ?></span>
<br>
<br>
<?php echo _("Do you really want to delete this Album?") ?>
<br>
<br>
<?php
$myAlbum = new Album();
$myAlbum->load($id);
?>
<?php echo $myAlbum->getHighlightTag() ?>
<br>
<br>
<b>
<?php echo $myAlbum->fields[title] ?>
</b>
<br>
<br>
<?php echo $myAlbum->fields[description] ?>
<br>
<?php echo makeFormIntro("delete_photo.php"); ?>
<input type="hidden" name="id" value="<?php echo $id ?>">
<input type="hidden" name="albumDelete" value=<?php echo $albumDelete ?>>
<input type="submit" name="confirm" value="<?php echo _("Delete") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<br>

<?php
	} else {
?>

<center>
<?php echo _("Do you really want to delete this photo?") ?>
<br>
<br>
<?php echo $gallery->album->getThumbnailTag($index) ?>
<br>
<?php echo $gallery->album->getCaption($index) ?>
<br>
<?php echo makeFormIntro("delete_photo.php"); ?>
<input type="hidden" name="id" value="<?php echo $id?>">
<input type="hidden" name="id2" value=2<?php echo $id2 ?>">    
<input type="submit" name="confirm" value="<?php echo _("Delete") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<br>

<?php
	}
} else {
	gallery_error(_("no album / index specified"));
}
?>

</body>
</html>
