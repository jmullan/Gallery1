<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	exit;
}
?>

<html>
<head>
  <title>Rename Album</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>

<?php
/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if ($newName) {
	$newName = str_replace("'", "", $newName);
	$newName = str_replace("`", "", $newName);
	$newName = strtr($newName, "\\/*?\"<>|& .+#", "-------------");
	$newName = ereg_replace("\-+", "-", $newName);
	$newName = ereg_replace("\-+$", "", $newName);
	$newName = ereg_replace("^\-", "", $newName);
	$newName = ereg_replace("\-$", "", $newName);
	if ($albumDB->renameAlbum($oldName, $newName)) {
		$albumDB->save();
		// need to account for nested albums by updating
		// the parent album when renaming an album
	        if ($gallery->album->fields[parentAlbumName]) {
			$parentName = $gallery->album->fields[parentAlbumName];
			if (isDebugging()) {
				print "parentName=".$parentName."<br>";
				print "newName=".$newName."<br>";
				print "oldName=".$oldName."<br>";
			}
			$parentAlbum = $albumDB->getAlbumbyName($parentName);
			for ($i=1; $i <= $parentAlbum->numPhotos(1); $i++) {
				if ($parentAlbum->isAlbumName($i) == $oldName) {
					$parentAlbum->setIsAlbumName($i,$newName);
					$parentAlbum->save();
					break;
				}
			}
		}
		// then we need to update the parentAlbumName field in the children
		for ($i=1; $i <= $gallery->album->numPhotos(1); $i++) {
			if ($gallery->album->isAlbumName($i)) {
				$childAlbum = $gallery->album->getNestedAlbum($i);
				$childAlbum->fields[parentAlbumName] = $newName;
				$childAlbum->save();
			}
		}
		dismissAndReload();
		return;
	} else {
		error("There is already an album with that name!");
	}
} else {
	$newName = $gallery->session->albumName;
}

?>
<br>
What do you want to name this album?
<br>
The name cannot contain any of
the following characters:  <br><center><b>\ / * ? " ' &amp; &lt; &gt; | . + # </b>or<b> spaces</b><br></center>
Those characters will be ignored in your new album name.

<br>
<?php echo makeFormIntro("rename_album.php", array("name" => "theform")); ?>
<input type=text name="newName" value=<?php echo $newName?>>
<input type=hidden name="oldName" value=<?php echo $gallery->session->albumName?>>
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
</body>
</html>
