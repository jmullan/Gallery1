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
<?
// Hack check
if (!$user->canWriteToAlbum($album)) {
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
if ($albumName) {
	if ($confirm) {
		$album->shufflePhotos();
		$album->save();
		dismissAndReload();
		return;
	} else {
?>

<center>
Do you really want to shuffle all the photos in this album?  This can't be undone.  You'll also need to reset the highlight photo (shown below).
<br>
<form>
<input type=submit name=confirm value="Yes">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<p>
<?= $album->getThumbnailTag($album->getHighlight()) ?>
<br>
<?= $album->fields["caption"] ?>

<?
	}
} else {
	error("no album specified");
}
?>

</body>
</html>
