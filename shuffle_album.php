<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
<? require_once('init.php'); ?>
<?
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>
<html>
<head>
  <title>Shuffle Album</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<?
if ($gallery->session->albumName) {
	if ($confirm) {
		$gallery->album->shufflePhotos();
		$gallery->album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Do you really want to shuffle all the photos in this album?  This operation can't be undone.
<br>
<form>
<input type=submit name=confirm value="Yes">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<p>
<?
if ($gallery->album->getHighlight()) {
	print $gallery->album->getThumbnailTag($gallery->album->getHighlight());
}
?>
<br>
<?= $gallery->album->fields["caption"] ?>

<?
	}
} else {
	error("no album specified");
}
?>

</body>
</html>
