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
if (!$user->canDeleteFromAlbum($album)) {
	exit;
}
	
if ($confirm && isset($index)) {
	if ($albumDelete) {
		print "entering sub";
		$myAlbum = $album->getNestedAlbum($index);
		$myAlbum->delete();
	}
	$album->deletePhoto($index);
	$album->save();
	dismissAndReload();
	return;
}
?>

<html>
<head>
  <title>Delete Photo</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>


<?
if ($album && isset($index)) {
	if (isset($albumDelete)) {
?>

<center>
<span class="popuphead">Delete Album</span>
<br>
<br>
Do you really want to delete this Album?
<br>
<br>
<?
$myAlbum = $album->getNestedAlbum($index);
?>
<?= $myAlbum->getHighlightTag() ?>
<br>
<br>
<b>
<?= $myAlbum->fields[title] ?>
</b>
<br>
<br>
<?= $myAlbum->fields[description] ?>
<br>
<form action=delete_photo.php>
<input type=hidden name=index value=<?= $index?>>
<input type=hidden name=albumDelete value=<?= $albumDelete?>>
<input type=submit name=confirm value="Delete">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<?
	} else {
?>

<center>
Do you really want to delete this photo?
<br>
<br>
<?= $album->getThumbnailTag($index) ?>
<br>
<?= $album->getCaption($index) ?>
<br>
<form action=delete_photo.php>
<input type=hidden name=index value=<?= $index?>>
<input type=submit name=confirm value="Delete">
<input type=submit value="Cancel" onclick='parent.close()'>
</form>
<br>

<?
	}
} else {
	error("no album / index specified");
}
?>

</body>
</html>
