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
  <title>Move Album</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<?
/* Read the album list */
$albumDB = new AlbumDB();

if ($albumName && isset($index)) {
	if (isset($newIndex)) {
		$albumDB->moveAlbum($user, $index, $newIndex);
		$albumDB->save();
		dismissAndReload();
		return;
	} else {
		$numAlbums = $albumDB->numAlbums($user);
?>

<center>
Select the new location of album <?=$album->fields["title"]?>:
<form name="theform">
<input type=hidden name="index" value="<?=$index?>">
<select name="newIndex">
<?
for ($i = 1; $i <= $numAlbums; $i++) {
	$sel = "";
	if ($i == $index) {
		$sel = "selected";
	} 
	echo "<option value=$i $sel> $i</option>";
}
?>
</select>
<input type=submit value="Move it!">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<p>
<?
if ($album->numPhotos(1)) {
	echo $album->getThumbnailTag($album->getHighlight());
}
?>

<?
	}
} else {
	error("no album / index specified");
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

