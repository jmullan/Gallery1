<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This file by Joan McGalliard
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
 * $Id: photo_owner.php 15631 2007-01-02 05:52:18Z jenst $
 */

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/lib/users.php');

list($save, $ownerUid, $id) = getRequestVar(array('save', 'ownerUid', 'id'));

// Hack checks
if (empty($gallery->album) || ! isset($gallery->session->albumName) ||
    !isset($id))
{
	printPopupStart(gTranslate('core', "Item Owner"));
	showInvalidReqMesg();
	exit;
}

if (!$gallery->user->isAdmin() &&
    !$gallery->user->isOwnerOfAlbum($gallery->album))
{
	printPopupStart(gTranslate('core', "Item Owner"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (isset($save) && $ownerUid) {
	// In case an invalid userid was given, this sets the new owner to nobody.
	$newOwner	= $gallery->userDB->getUserByUid($ownerUid);
	$newOwnerUid	= $newOwner->getUid();

	$gallery->album->setItemOwnerById($id, $newOwnerUid);

	$gallery->album->save(array(
		i18n("New owner %s for %s"),
			$newOwner->printableName('!!FULLNAME!! (!!USERNAME!!)'),
			makeAlbumURL($gallery->album->fields["name"], $id))
	);

	dismissAndReload();
	exit;
}

list($specialUsers, $users, $allUsers) = buildUsersList(true);

$ownerUid = $gallery->album->getItemOwnerById($id);
if ($gallery->userDB->getUserByUid($ownerUid) == NULL) {
	$nobody		= $gallery->userDB->getNobody();
	$ownerUid	= $nobody->getUid();
}

printPopupStart(gTranslate('core', "Item Owner"));

$index = $gallery->album->getPhotoIndex($id);
echo $gallery->album->getThumbnailTag($index);
echo "\n<br>";
echo $gallery->album->getCaption($index);

echo "\n<br>";
echo makeFormIntro('item_owner.php',
	array('name' => 'item_owner_form'),
	array('type' => 'popup', 'id' => $id));

if ($gallery->user->isAdmin) {
	echo gTranslate('core', "Owner: ");
	echo drawSelect("ownerUid", $allUsers, $ownerUid, 1);
}

echo "\n<br><br>";
echo gSubmit('save', gTranslate('core', "_Save"));
echo gButton('done', gTranslate('core', "_Close"), 'parent.close()');
?>
</form>
</div>

</body>
</html>
