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
if (!isset($gallery->album) || !$gallery->user->canWriteToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action !");
	exit;
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Rename Album") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<center>
<p class="popuphead"><?php echo _("Rename Album") ?></p>
<div class="popup">
<?php

if (!isset($useLoad)) {
	$useLoad="";
}

/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if (!empty($newName)) {
	$dismiss = 0;
	$newName = str_replace("'", "", $newName);
	$newName = str_replace("`", "", $newName);
	$newName = strtr($newName, "\\/*?\"<>|& .+#()", "---------------");
	$newName = ereg_replace("\-+", "-", $newName);
	$newName = ereg_replace("\-+$", "", $newName);
	$newName = ereg_replace("^\-", "", $newName);
	$newName = ereg_replace("\-$", "", $newName);
	if ($oldName == $newName) {
		$dismiss = 1;
	} elseif ($albumDB->renameAlbum($oldName, $newName)) {
		$albumDB->save();
		// need to account for nested albums by updating
		// the parent album when renaming an album
	        if ($gallery->album->fields['parentAlbumName']) {
			$parentName = $gallery->album->fields['parentAlbumName'];
			if (isDebugging()) {
				print "parentName=".$parentName."<br>";
				print "newName=".$newName."<br>";
				print "oldName=".$oldName."<br>";
			}
			$parentAlbum = $albumDB->getAlbumByName($parentName);
			for ($i=1; $i <= $parentAlbum->numPhotos(1); $i++) {
				if ($parentAlbum->getAlbumName($i) == $oldName) {
					$parentAlbum->setAlbumName($i,$newName);
					$parentAlbum->save();
					break;
				}
			}
		}
		// then we need to update the parentAlbumName field in the children
		for ($i=1; $i <= $gallery->album->numPhotos(1); $i++) {
			if ($gallery->album->isAlbum($i)) {
				$childAlbum = $gallery->album->getNestedAlbum($i);
				$childAlbum->fields['parentAlbumName'] = $newName;
				$childAlbum->save();
			}
		}
		$dismiss = 1;
	} else {
		echo gallery_error(_("There is already an album with that name!"));
	}

	// Dismiss and reload if requested
	if ($dismiss) {
		if ($useLoad == 1) {
			dismissAndLoad(makeAlbumUrl($newName));
		}
		else {
			dismissAndReload();
		}
		return;
	}

} else {
	$newName = $gallery->session->albumName;
}

?>
<br>
<?php echo _("What do you want to name this album?") ?>
<br>
<?php echo _("The name cannot contain any of the following characters") ?>:
<br><b>\ / * ? &quot; &rsquo; &amp; &lt; &gt; | . + # ( )</b><?php echo _("or") ?><b> <?php echo _("spaces") ?></b><br>
<p><?php echo _("Those characters will be ignored in your new album name.") ?></p>

<br>
<?php echo makeFormIntro("rename_album.php", array("name" => "theform")); ?>
<input type="text" name="newName" value="<?php echo $newName?>">
<input type="hidden" name="oldName" value="<?php echo $gallery->session->albumName?>">
<input type="hidden" name="useLoad" value="<?php echo $useLoad?>">    
<p>
<input type="submit" name="rename" value="<?php echo _("Rename") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--   
// position cursor in top form field
document.theform.newName.focus();
//-->
</script>

</div>
</center>
<?php print gallery_validation_link("rename_album.php",true); ?>
</body>
</html>
