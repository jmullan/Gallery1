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

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album) && !($gallery->album->isItemOwner($gallery->user->getUid(), $index) && $gallery->album->getItemOwnerModify())) {
	echo _("You are no allowed to perform this action !");
	exit;
}

doctype();
?>

<html>
<head>
  <title><?php echo _("Rotate/Flip Photo") ?></title>
  <?php common_header(); ?>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache"> 
  <META HTTP-EQUIV="expires" CONTENT="0"> 
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Rotate/Flip Photo") ?></p>

<span class="popup">
<?php
if ($gallery->session->albumName && isset($index)) {
	if (isset($rotate) && !empty($rotate)) {
		echo _("Rotating/Flipping photo.");
		echo "\n<br>";
		echo _("(this may take a while)");

		my_flush();
                set_time_limit($gallery->app->timeLimit);
		$gallery->album->rotatePhoto($index, $rotate);
		$gallery->album->save(array(i18n("Image %s rotated"),
				       	makeAlbumURL($gallery->album->fields["name"], 
						$gallery->album->getPhotoId($index))));
		reload();
		print "<p>" . _("Manipulate again?");
	} else {
		echo _("How do you want to manipulate this photo?");
	} 
?>
<br><br>
<?php $args = array("albumName" => $gallery->album->fields["name"], "index" => $index); ?>
<?php echo _("Rotate") ?>: [ 
<?php $args["rotate"] = "90"; ?>
<a href="<?php echo makeGalleryUrl("rotate_photo.php", $args)?>"><?php echo _("Counter-Clockwise") ?> 90&deg;</a>
 | 
	<?php $args["rotate"] = "180"; ?>
<a href="<?php echo makeGalleryUrl("rotate_photo.php", $args)?>">180&deg;</a>
 | 
	<?php $args["rotate"] = "-90"; ?>
<a href="<?php echo makeGalleryUrl("rotate_photo.php", $args)?>"><?php echo _("Clockwise") ?> 90&deg;</a>
]<br><br><?php echo _("Flip") ?>: [ 
	<?php $args["rotate"] = "fh"; ?>
<a href="<?php echo makeGalleryUrl("rotate_photo.php", $args)?>"><?php echo _("Horizontal") ?></a>
 | 
	<?php $args["rotate"] = "fv"; ?>
<a href="<?php echo makeGalleryUrl("rotate_photo.php", $args)?>"><?php echo _("Vertical") ?></a>
 ]<br><br>
<a href="javascript:void(parent.close())"><?php echo _("Close") ?></a>
<br>

<p>
<?php echo $gallery->album->getThumbnailTag($index) ?>

<?php
} else {
	echo gallery_error(_("no album / index specified"));
}
?>

</span>
</center>
<?php print gallery_validation_link("rotate_photo.php"); ?>
</body>
</html>
