<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 */
?>
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	exit;
}
?>
<?

if (!strcmp($submit, "Save") && $owner) {
	$gallery->album->setItemOwnerById($id, $owner);
	$gallery->album->save();
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
<html>
<head>
  <title>Change Owner</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead"> Change owner </span>
<br>
<? $index=$gallery->album->getPhotoIndex($id) ?>
<br>
<br>
<?= $gallery->album->getThumbnailTag($index) ?>
<br>
<?= $gallery->album->getCaption($index) ?>
<br>

<?= makeFormIntro("photo_owner.php", 
			array("name" => "photoowner_form")) ?>

<? if ($gallery->user->isAdmin) { ?>
Owner: <?= drawSelect("owner", $uAll, $owner, 1); ?>
<? } ?><p>

<input type=hidden name="id" value="<? echo $id ?>">
<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Done" onclick='parent.close()'>
</form>

</body>
</html>
