<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

list($reorder, $index, $newParentAlbumName, $newIndex) =
	getRequestVar(array('reorder', 'index', 'newParentAlbumName', 'newIndex'));

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName)
	|| !isset($reorder))
{
	printPopupStart(gTranslate('core', "Move Top Album"));
	showInvalidReqMesg();
	exit;
}

// Hack check
if (!$gallery->user->canWriteToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Move Top Album"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if($reorder == 0) {
	printPopupStart(gTranslate('core', "Move Top Album"));
}
else {
	printPopupStart(gTranslate('core', "Reorder Top Album"));
}

/* Read the album list */
$albumDB = new AlbumDB(FALSE);

// Moving album to a nested location
if (isset($newParentAlbumName) && $reorder == 0) {
	if ($gallery->album->fields['name'] != $newParentAlbumName) {
		$newParentAlbum = $albumDB->getAlbumByName($newParentAlbumName);

		if(!$newParentAlbum) {
			echo gallery_error(gTranslate('core', "You chose an invalid new parent album?"));
			$error = true;
		}
		else {
			$old_parent = $gallery->album->fields['parentAlbumName'];
			$gallery->album->fields['parentAlbumName'] = $newParentAlbumName;

			// Regenerate highlight if needed..
			if ($gallery->app->highlight_size != $newParentAlbumName->fields["thumb_size"]) {
				$hIndex = $gallery->album->getHighlight();
				if (isset($hIndex)) {
					$hPhoto =& $gallery->album->getPhoto($hIndex);
					$hPhoto->setHighlight($gallery->album->getAlbumDir(), true, $gallery->album);
				}
			}

			if ($old_parent == 0) {
				$old_parent = '.root';
			}

			$gallery->album->save(array(i18n("Album moved from %s to %s"), $old_parent, $newParentAlbumName));

			$newParentAlbum->addNestedAlbum($gallery->album->fields['name']);
			if ($newParentAlbum->numPhotos(1) == 1) {
				$newParentAlbum->setHighlight(1);
			}

			$newParentAlbum->save(array(i18n("New subalbum %s. Moved from %s"),
									$gallery->album->fields['name'],
									$old_parent)
			);
		}
	}

	if(!isset($error)) {
		dismissAndReload();
		exit;
	}
}
elseif (isset($newIndex) && $reorder == 1) {
	if(!isValidGalleryInteger($index) || !isValidGalleryInteger($newIndex)) {
		echo gallery_error(gTranslate('core', "Given start and end index are wrong?"));
	}
	else {
		$albumDB->moveAlbum($gallery->user, $index, $newIndex);
		$albumDB->save();
		dismissAndReload();
		exit;
	}
}

$visibleAlbums = $albumDB->getVisibleAlbums($gallery->user);

printf(gTranslate('core', "Select the new location of album: '%s%s%s'"),
		'<b>',
		$gallery->album->fields["title"],
		'</b>'
);

// Move
if ($reorder == 0) {
	echo "\n<br>" . gTranslate('core', "Your album will be moved into the album you choose below.");
	echo '<p>' .  $gallery->album->getHighlightTag() . '</p>';

	echo makeFormIntro('move_album.php',
				array(),
				array('type' => 'popup', 'reorder' => $reorder));

	list($uptodate, $albumOptionList) = albumOptionList(false, true);

	echo drawSelect2('newParentAlbumName', $albumOptionList);
?>
		<br><br>
		<?php echo gSubmit('move', gTranslate('core', "Move to Album!")) ;?>
		<?php echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()'); ?>
</form>
<?php
}
// Reorder
else {
	echo "\n<br>" . gTranslate('core', "Your album will be moved to the position you choose below.");
	echo '<p>' .  $gallery->album->getHighlightTag() . '</p>';

	echo makeFormIntro('move_album.php',
				array(),
				array('type' => 'popup', 'index' => $index, 'reorder' => $reorder));

	foreach ($visibleAlbums as $albumIndex => $album) {
		$i = $albumIndex+1;
		$indexArray[$i] = "$i . ". $album->fields['title'];
	}

	echo drawSelect('newIndex', $indexArray, $index);

	echo "\n<br><br>\n";
	echo gSubmit('move', gTranslate('core', "Move it!")) ;
	echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()');

echo "</form>";

} // End Reorder

?>

</div>

<?php print gallery_validation_link("move_album.php", true, array('index' => $index)); ?>
</body>
</html>
