<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($save, $owner, $id) = getRequestVar(array('save', 'owner', 'id'));

// Hack check
if (!$gallery->user->isAdmin() &&
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

if ( isset($save) && $owner) {
    $gallery->album->setItemOwnerById($id, $owner);
    $user = $gallery->userDB->getUserByUid($owner);
    $gallery->album->save(array(
		i18n("New owner %s for %s"),
		$user->printableName('!!FULLNAME!! (!!USERNAME!!)'),
		makeAlbumURL($gallery->album->fields["name"], $id)
      )
    );

    doctype();
    echo "\n<html>";
    dismissAndReload();
    return;
}

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $gallery->userDB->getNobody();
$owner = $nobody->getUid();

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uAll[$uid] = $tmpUser->getFullName()." (".$tmpUser->getUsername().")";
}

$owner = $gallery->album->getItemOwnerById($id);
if ($gallery->userDB->getUserByUid($owner) == NULL) {
	$nobody = $gallery->userDB->getNobody();
	$owner = $nobody->getUid();

}

asort($uAll);

printPopupStart(_("Change Owner"));

$index = $gallery->album->getPhotoIndex($id);
echo $gallery->album->getThumbnailTag($index);
echo "\n<br>";
echo $gallery->album->getCaption($index);

echo "\n<br>";
echo makeFormIntro('photo_owner.php',
	array('name' => 'photoowner_form'),
	array('type' => 'popup'));

if ($gallery->user->isAdmin) {
	echo gTranslate('core', "Owner: ");
	echo drawSelect("owner", $uAll, $owner, 1);
}
?>

	<input type="hidden" name="id" value="<?php echo $id ?>">
	<input type="submit" name="save" value="<?php echo _("Save") ?>" class="g-button">
	<input type="button" name="done" value="<?php echo _("Done") ?>" onclick="parent.close()" class="g-button">
</form>
</div>
<?php print gallery_validation_link("photo_owner.php", true, array('id' => $id)); ?>
</body>
</html>
