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

require(dirname(__FILE__) . '/init.php');

if (isset($id)) {
        $index = $gallery->album->getPhotoIndex($id);
}

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) 
	&& (!$gallery->album->getItemOwnerDelete()
	|| !$gallery->album->isItemOwner($gallery->user->getUid(), $index))) {
	echo _("You are no allowed to perform this action !");
	exit;
}

doctype();
echo "\n<html>";

if (isset($action) && $action == 'delete' && isset($id)) {
	if (!empty($albumDelete)) {
		/* Track down the corresponding photo index and remove it */
		$index = 0;
		for ($i = 1; $i <= sizeof($gallery->album->photos); $i++) {
		    $photo = $gallery->album->getPhoto($i);
		    if ($photo->isAlbum() && !strcmp($photo->getAlbumName(), $id)) {
			$myAlbum = new Album();
			$myAlbum->load($id, false);
			if ($myAlbum->fields['guid'] == $albumDelete) {
				/* Found it */
				$index = $i;
				$albumMatch = 1;
				break;
			}
		    }
		}
	}

	// Prevent a user from pressing delete twice out of impatience and
	// deleting two albums by mistake
	if (!isset($albumDelete) || isset($albumMatch)) {
		$gallery->album->deletePhoto($index);
		$gallery->album->fields['guid'] = md5(uniqid(rand(), true));    // Update guid to reflect change in album contents
		$gallery->album->save(array(i18n("%s removed"), $id));
	}

	if (isset($nextId) && !empty($nextId)) {
	    dismissAndLoad(makeAlbumUrl($gallery->session->albumName, $nextId));
	} else {
	    dismissAndLoad(makeAlbumUrl($gallery->session->albumName));
	}
	return;
}
?>

<head>
  <title><?php echo isset($albumDelete) ? _("Delete Album") : _("Delete Photo") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<div align="center">
<?php
if ($gallery->album && isset($id)) {
	echo makeFormIntro("delete_photo.php", array('name' => 'deletephoto_form', 
			'onsubmit' => "deletephoto_form.confirm.disabled = true;"));
	if (isset($albumDelete)) {
?>

<p class="popuphead"><?php echo _("Delete Album") ?></p>

<p class="popup">
	<?php echo _("Do you really want to delete this Album?") ?>
</p>

<?php
$myAlbum = new Album();
$myAlbum->load($id);
?>
<p>
<?php echo $myAlbum->getHighlightTag() ?>
</p>

<b>
<?php echo $myAlbum->fields['title'] ?>
</b>
<br>
<br>
<?php echo $myAlbum->fields['description'] ?>
<br>
<input type="hidden" name="id" value="<?php echo $id ?>">
<input type="hidden" name="albumDelete" value="<?php echo $myAlbum->fields['guid']; ?>">
<?php
	} else {
?>
<p class="popuphead"><?php echo _("Delete Photo") ?></p>

<?php echo _("Do you really want to delete this photo?") ?>

<p><?php echo $gallery->album->getThumbnailTag($index) ?></p>

<p><?php echo $gallery->album->getCaption($index) ?></p>

<input type="hidden" name="id" value="<?php echo $id?>">
<?php 
		if (isset($nextId)) {
			echo "\n". '<input type="hidden" name="nextId" value="'. $nextId .'"> ';
		} 
	}
?>
<input type="hidden" name="action" value="">
<input type="submit" name="confirm" value="<?php echo _("Delete") ?>" onclick="deletephoto_form.action.value='delete'">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<br>
<?php
} else {
	echo gallery_error(_("no album / index specified"));
}
?>
</div>

<?php print gallery_validation_link("delete_photo.php", false, array('id' => $id, 'index' => $index)); ?>
</body>
</html>
