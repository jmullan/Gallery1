<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2001 Bharat Mediratta
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
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>

<html>
<head>
  <title>Rotate Photo</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<?
if ($gallery->session->albumName && isset($index)) {
	if ($rotate) {
?>
	<center>
	 Rotating photo.
	 <br>
	 (this may take a while)
	</center>
<?
		my_flush();
		$gallery->album->rotatePhoto($index, $rotate);
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
How do you want to rotate this photo?
<br>
<? $args = array("albumName" => $gallery->album->fields["name"], "index" => $index); ?>
<? $args["rotate"] = "90"; ?>
<a href=<?=makeGalleryUrl("rotate_photo.php", $args)?>>Counter-Clockwise 90&ordm;</a>
/
<? $args["rotate"] = "180"; ?>
<a href=<?=makeGalleryUrl("rotate_photo.php", $args)?>>Flip 180&ordm;</a>
/
<? $args["rotate"] = "-90"; ?>
<a href=<?=makeGalleryUrl("rotate_photo.php", $args)?>>Clockwise 90&ordm;</a>
/
<a href="javascript:void(parent.close())">Cancel</a>
<br>

<p>
<?= $gallery->album->getThumbnailTag($index) ?>

<?
	}
} else {
	error("no album / index specified");
}
?>

</body>
</html>

