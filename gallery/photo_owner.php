<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	echo _("You are no allowed to perform this action !");
	exit;
}

doctype();
echo "\n<html>";

if ( isset($save) && $owner) {
	$gallery->album->setItemOwnerById($id, $owner);
	$user=$gallery->userDB->getUserByUid($owner);
	$gallery->album->save(array(i18n("New owner %s for %s"),  
				$user->printableName('!!FULLNAME!! (!!USERNAME!!)'),
				makeAlbumURL($gallery->album->fields["name"], $id)));


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

$owner=$gallery->album->getItemOwnerById($id);
if ($gallery->userDB->getUserByUid($owner) == NULL)
{
	$nobody = $gallery->userDB->getNobody(); 
	$owner = $nobody->getUid();

}

asort($uAll);


?>
<head>
  <title><?php echo _("Change Owner") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Change Owner") ?></p>
<?php 
	$index=$gallery->album->getPhotoIndex($id);
	echo $gallery->album->getThumbnailTag($index);
	$gallery->album->getCaption($index);
?>

<p>
<?php 
	echo makeFormIntro("photo_owner.php", array("name" => "photoowner_form"));
	if ($gallery->user->isAdmin) {
		echo _("Owner") .": ";
		echo drawSelect("owner", $uAll, $owner, 1);
	}
?>

<p>
	<input type="hidden" name="id" value="<?php echo $id ?>">
	<input type="submit" name="save" value="<?php echo _("Save") ?>">
	<input type="button" name="done" value="<?php echo _("Done") ?>" onclick='parent.close()'>
</p>
</form>
</center>

<?php print gallery_validation_link("photo_owner.php", true, array('id' => $id)); ?>
</body>
</html>
