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
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>

<html>
<head>
  <title><?php echo _("Move Album") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<?php
/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if ($gallery->session->albumName && isset($index)) {

	if (isset($newAlbum)) { // moving album to a nested location
		if ($gallery->album->fields[name] != $newAlbum) {
			$gallery->album->fields[parentAlbumName] = $newAlbum;
			$gallery->album->save();
			$newAlbum = $albumDB->getAlbumbyName($newAlbum);
			$newAlbum->addNestedAlbum($gallery->album->fields[name]);
			$newAlbum->save();
		}
		dismissAndReload();
		return;
	}
	if (isset($newIndex)) {
		$albumDB->moveAlbum($gallery->user, $index, $newIndex);
		$albumDB->save();
		dismissAndReload();
		return;
	} else {
		$numAlbums = $albumDB->numAlbums($gallery->user);
?>

<center>
<?php echo _("Select the new location of album") ?> <?php echo $gallery->album->fields["title"] ?>:

<?php echo makeFormIntro("move_album.php", array("name" => "theform")); ?>
<input type="hidden" name="index" value="<?php echo $index ?>">
<select name="newIndex">
<?php
for ($i = 1; $i <= $numAlbums; $i++) {
	$sel = "";
	if ($i == $index) {
		$sel = "selected";
	} 
	echo "<option value=\"$i\" $sel> $i</option>";
}
?>
</select>
<input type="submit" name="move" value="<?php _("Move it!") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<p>
<?php
if ($gallery->album->numPhotos(1)) {
	echo $gallery->album->getThumbnailTag($gallery->album->getHighlight());
}
?>
<p>
<br>
<b><?php echo _("OR") ?></b>
<br>
<br>
<?php echo _("Nest within another Album:") ?>
<p>
<?php echo makeFormIntro("move_album.php", array("name" => "move_to_album_form")); ?>
<input type="hidden" name="index" value="<?php echo $index ?>">
<select name="newAlbum">
<?php
printAlbumOptionList(0,1)  
?>
</select>
<br>
<br>
<input type="submit" name="move" value="<?php echo _("Move to Album!") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<?php
	}
} else {
	gallery_error(_("no album / index specified"));
}
?>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.newIndex.focus();
//-->
</script>

</body>
</html>
