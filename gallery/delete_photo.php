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

list($index, $formaction, $GUID, $nextId, $id) =
	getRequestVar(array('index', 'formaction', 'GUID', 'nextId', 'id'));

printPopupStart(gTranslate('core', "Delete album item"));

// Hack checks
if (! isset($gallery->album) || ! isset($gallery->session->albumName)) {
	showInvalidReqMesg();
	exit;
}


if(empty($index) && !empty($id)) {
	$index = $gallery->album->getAlbumIndex($id);
}

if(! $item = $gallery->album->getPhoto($index)) {
	showInvalidReqMesg();
	exit;
}

if ($gallery->album->isAlbum($index)) {
	$myAlbum = $gallery->album->getNestedAlbum($index, false);
}

// Hack check
if (!$gallery->user->canDeleteFromAlbum($gallery->album) &&
	!($gallery->album->getItemOwnerDelete() && $gallery->album->isItemOwner($gallery->user->getUid(), $index)))
{
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$id = $item->getPhotoId();

if (isset($formaction) && $formaction == 'delete') {
	if($GUID == $id) {
		$gallery->album->deletePhoto($index);
		$gallery->album->fields['guid'] = md5(uniqid(mt_rand(), true));	// Update guid to reflect change in album contents

		$gallery->album->save(array(i18n("%s removed"), $id));

		if (isset($nextId) && !empty($nextId)) {
			dismissAndLoad(makeAlbumUrl($gallery->session->albumName, $nextId));
		}
		else {
			dismissAndLoad(makeAlbumUrl($gallery->session->albumName));
		}
		includeTemplate('overall.footer');
		echo "\n\t</body>\n</html>";
		exit;
	}
	else {
		echo gallery_error(gTranslate('core', "It seems you double clicked the delete button, or refreshed this dialog in an other way. If you really want to delete THIS item, press delete again."));
	}
}

if (isset($myAlbum)) {
	echo makeFormIntro('delete_photo.php',
		array('name' => 'deletephoto_form', 'onsubmit' => 'deletephoto_form.confirm.disabled = true;'),
		array('type' => 'popup')
	);

	echo gTranslate('core', "Do you really want to delete this album?");

	echo "<p>" . $myAlbum->getHighlightTag() . "</p>\n";
	echo '<p class="g-emphasis">' . $myAlbum->fields['title']   . "</p>\n";
}
else {
	echo gTranslate('core', "Do you really want to delete this item?") ;
	echo makeFormIntro('delete_photo.php',
		array('name' => 'deletephoto_form', 'onsubmit' => 'deletephoto_form.confirm.disabled = true;'),
		array('type' => 'popup')
	);

	echo "  <p>" . $gallery->album->getThumbnailTag($index) . "</p>\n";
	if($gallery->album->getCaption($index)) {
		echo "  <p>" . $gallery->album->getCaption($index)  . "</p>\n";
	}

	if (isset($nextId)) {
		echo gInput('hidden', 'nextId', null, false, $nextId);
	}
}

echo gInput('hidden', 'GUID', null, false, $id);
echo gInput('hidden', 'index', null, false, $index);
echo gInput('hidden', 'formaction', null, false, '');
echo gButton('confirm', gTranslate('core', "Delete"), "deletephoto_form.formaction.value='delete'; deletephoto_form.submit()");
echo gButton('cancel', gTranslate('core', "Cancel"), 'parent.close()');
?>
</form>
</div>
<?php print gallery_validation_link("delete_photo.php", true, array('id' => $id, 'index' => $index)); ?>
</body>
</html>
