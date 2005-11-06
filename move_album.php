<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($reorder, $index, $newAlbum, $newIndex) = getRequestVar(array('reorder', 'index', 'newAlbum', 'newIndex'));

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Move Album") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Move Album") ?></div>
<div class="popup" align="center">
<?php
/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if ($gallery->session->albumName && isset($index)) {
    if (isset($newAlbum)) { // moving album to a nested location
	if ($gallery->album->fields['name'] != $newAlbum) {
	    $old_parent = $gallery->album->fields['parentAlbumName'];
	    $gallery->album->fields['parentAlbumName'] = $newAlbum;
			
	    // Regenerate highlight if needed..
	    if ($gallery->app->highlight_size != $newAlbum->fields["thumb_size"]) {
		$hIndex = $gallery->album->getHighlight();
		if (isset($hIndex)) {
		    $hPhoto =& $gallery->album->getPhoto($hIndex);
		    $hPhoto->setHighlight($gallery->album->getAlbumDir(), true, $gallery->album);
		}
	    }
			
	    if ($old_parent == 0) {
		$old_parent=".root";
	    }
	
	    $gallery->album->save(array(i18n("Album moved from %s to %s"),
					$old_parent,
					$newAlbum));

	    $newAlbum = $albumDB->getAlbumByName($newAlbum);
	    $newAlbum->addNestedAlbum($gallery->album->fields['name']);
	    if ($newAlbum->numPhotos(1) == 1) {
		$newAlbum->setHighlight(1);
	    }
	
	    $newAlbum->save(array(i18n("New subalbum %s, moved from %s"),
				$gallery->album->fields['name'], 
				$old_parent));
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
	$visibleAlbums = $albumDB-> getVisibleAlbums($gallery->user);

	echo sprintf(gTranslate('core', "Select the new location of album: %s"), $gallery->album->fields["title"]);
	echo "\n<br>" . gTranslate('core', "Your Album will be moved to the position you choose below.");
	echo '<p>' .  $gallery->album->getHighlightTag() . '</p>';

	if (!empty($reorder)) { // Reorder, intra-album move
	    echo makeFormIntro("move_album.php", 
		array("name" => "theform"),
		array("type" => "popup")); 
?>
<input type="hidden" name="index" value="<?php echo $index ?>">
<select name="newIndex">
<?php
	foreach ($visibleAlbums as $albumIndex => $album) {
	    $i = $albumIndex+1;
	    $sel = "";
	    if ($i == $index) {
		$sel = "selected";
	    } 
	
	    echo "\n\t<option value=\"$i\" $sel>$i . ". $album->fields['title'] ."</option>";
	}
?>
</select>
<input type="submit" name="move" value="<?php echo gTranslate('core', "Move it!") ?>">
<input type="button" name="cancel" value="<?php echo gTranslate('core', "Cancel") ?>" onclick='parent.close()'>
</form>

<p>
<?php
	} else { // Reorder, trans-album move
	    echo gTranslate('core', "Nest within another Album:"); 
	    echo makeFormIntro("move_album.php", 
		array("name" => "theform"),
		array("type" => "popup"));
?>
<input type="hidden" name="index" value="<?php echo $index ?>">
<select name="newAlbum">
<?php
	    printAlbumOptionList(0,1)  
?>
</select>
<br><br>

<input type="submit" name="move" value="<?php echo gTranslate('core', "Move to Album!") ?>">
<input type="button" name="cancel" value="<?php echo gTranslate('core', "Cancel") ?>" onclick='parent.close()'>
</form>
<?php
	} // End Reorder
    }
} else {
    echo gallery_error(gTranslate('core', "no album / index specified"));
}
?>

<script language="javascript1.2" type="text/JavaScript">
<!--   
<?php if (!empty($reorder)) { ?>
// position cursor in top form field
document.theform.newIndex.focus();
<?php } else { ?>
document.theform.newAlbum.focus();
<?php } ?>
// -->
</script>
</div>

<?php print gallery_validation_link("move_album.php", true, array('index' => $index)); ?>
</body>
</html>
