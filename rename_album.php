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
<? require('init.php'); ?>
<?
// Hack check
if (!$user->canWriteToAlbum($album)) {
	exit;
}
?>

<html>
<head>
  <title>Rename Album</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>

<?
/* Read the album list */
$albumDB = new AlbumDB();

if ($newName) {
	$newName = str_replace("'", "", $newName);
	$newName = str_replace("`", "", $newName);
	$newName = strtr($newName, "\\/*?\"<>|& ", "----------");
	$newName = ereg_replace("\-+", "-", $newName);
	$newName = ereg_replace("\-+$", "", $newName);
	$albumDB->renameAlbum($oldName, $newName);
	$albumDB->save();
	dismissAndReload();
	return;
} else {
}

?>

What do you want to name this album?  The name cannot contain any of
the following characters:  <br><center><b>\ / * ? " ' &amp; &lt; &gt; | </b>or<b> spaces</b><br></center>
Those characters will be ignored in your new album name.

<br>
<form name="theform">
<input type=text name="newName" value=<?=$albumName?>>
<input type=hidden name="oldName" value=<?=$albumName?>>
<p>
<input type=submit value="Rename">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--   
// position cursor in top form field
document.theform.newName.focus();
//-->
</script>
