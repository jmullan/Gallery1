<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($oldName, $newName, $useLoad) = getRequestVar(array('oldName', 'newName', 'useLoad'));

// Hack check
if (!isset($gallery->album) || !$gallery->user->canWriteToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

printPopupStart(gTranslate('core', "Rename Album"));

if (!isset($useLoad)) {
	$useLoad = '';
}

/* Read the album list */
$albumDB = new AlbumDB(FALSE);

if (!empty($newName)) {
	$dismiss = 0;
	$newName = str_replace("'", "", $newName);
	$newName = str_replace("`", "", $newName);
	$newName = strtr($newName, "%\\/*?\"<>|& .+#(){}~", "-------------------");
	$newName = ereg_replace("\-+", "-", $newName);
	$newName = ereg_replace("\-+$", "", $newName);
	$newName = ereg_replace("^\-", "", $newName);
	$newName = ereg_replace("\-$", "", $newName);
	if ($oldName == $newName || empty($newName)) {
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
	    echo infoBox(array(array(
    	    'type' => 'error',
    	    'text' => gTranslate('core', "There is already an album with that name!")))
	    );
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

echo gTranslate('core', "What do you want to name this album?");
echo "\n<br><br>";
echo gTranslate('core', "The name cannot contain any of the following characters") ?>:
<br><b>% \ / * ? &quot; &rsquo; &amp; &lt; &gt; | . + # ( )</b><?php echo gTranslate('core', "or") ?><b> <?php echo gTranslate('core', "spaces") ?></b>
<br>
<?php echo gTranslate('core', "Those characters will be ignored in your new album name."); ?>

<?php echo makeFormIntro('rename_album.php',  array(), array('type' => 'popup')); ?>
<p>
<input type="text" name="newName" value="<?php echo $newName; ?>">
<input type="hidden" name="oldName" value="<?php echo $gallery->session->albumName?>">
<input type="hidden" name="useLoad" value="<?php echo $useLoad; ?>">
</p>

<?php echo gSubmit('rename', gTranslate('core', "_Rename")); ?>
<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</form>

<br><br>
<?php
echo infoBox(array(array(
    'type' => 'information',
    'text' => gTranslate('core', "This it not the title of the album, its the filename of the directory on your webserver. The name is also used in the url.")
)));

?>
<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.g1_form.newName.focus();
//-->
</script>
</div>

</body>
</html>
