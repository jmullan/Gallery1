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
if (!$gallery->user->canWriteToAlbum($gallery->album) && !($gallery->album->isItemOwner($gallery->user->getUid(), $index) && $gallery->album->getItemOwnerModify())) {
	exit;
}
?>

<html>
<head>
  <title><?php echo _("Rotate/Flip Photo") ?></title>
  <?php echo getStyleSheetLink() ?>
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache"> 
  <META HTTP-EQUIV="expires" CONTENT="0"> 
</head>
<body dir=<?php echo $gallery->direction ?>>

<?php
if ($gallery->session->albumName && isset($index)) {
	if ($rotate) {
?>
	<center>
	 <?php echo _("Rotating/Flipping photo.") ?>
	<br>
	 <?php echo _("(this may take a while)") ?>
<?php
		my_flush();
                set_time_limit($gallery->app->timeLimit);
		$gallery->album->rotatePhoto($index, $rotate);
		$gallery->album->save();
		reload();
		print "<p>Manipulate again?";
	} else {
?>

<center>
	<?php echo _("How do you want to manipulate this photo?") ?>
<?php } ?>
<br /><br />
<?php $args = array("albumName" => $gallery->album->fields["name"], "index" => $index); ?>
<?php echo _("Rotate") ?>: [ 
<?php $args["rotate"] = "90"; ?>
<a href=<?php echo makeGalleryUrl("rotate_photo.php", $args)?>><?php echo _("Counter-Clockwise") ?> 90&deg;</a>
 | 
	<?php $args["rotate"] = "180"; ?>
<a href=<?php echo makeGalleryUrl("rotate_photo.php", $args)?>>180&deg;</a>
 | 
	<?php $args["rotate"] = "-90"; ?>
<a href=<?php echo makeGalleryUrl("rotate_photo.php", $args)?>><?php echo _("Clockwise") ?> 90&deg;</a>
]<br /><br /><?php echo _("Flip") ?>: [ 
	<?php $args["rotate"] = "fh"; ?>
<a href=<?php echo makeGalleryUrl("rotate_photo.php", $args)?>><?php echo _("Horizontal") ?></a>
 | 
	<?php $args["rotate"] = "fv"; ?>
<a href=<?php echo makeGalleryUrl("rotate_photo.php", $args)?>><?php echo _("Vertical") ?></a>
 ]<br /><br />
<a href="javascript:void(parent.close())"><?php echo _("Close") ?></a>
<br />

<p>
<?php echo $gallery->album->getThumbnailTag($index) ?>

<?php
} else {
	gallery_error(_("no album / index specified"));
}
?>

</body>
</html>
