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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($allUid, $submit_read, $readUid, $submit_text, $textUid, $submit_add, $addUid) =
	getRequestVar(array('allUid', 'submit_read', 'readUid', 'submit_text', 'textUid', 'submit_add', 'addUid'));

list($submit_write, $writeUid, $submit_delete, $deleteUid, $submit_createSub, $createSubUid) =
	getRequestVar(array('submit_write', 'writeUid', 'submit_delete', 'deleteUid', 'submit_createSub', 'createSubUid'));

list($submit_viewFullImages, $viewFullImagesUid, $submit_addComments, $addCommentsUid) =
	getRequestVar(array('submit_viewFullImages', 'viewFullImagesUid', 'submit_addComments', 'addCommentsUid'));


list($submit_viewComments, $viewCommentsUid, $save, $ownerUid) =
	getRequestVar(array('submit_viewComments', 'viewCommentsUid', 'save', 'ownerUid'));

// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}
?>
<?php
$changed=0;
if (isset($allUid) && isset($submit_read) && strchr($submit_read, ">")) {
	$gallery->album->setRead($allUid, 1);
	$changed++;
} else if (isset($readUid) && isset($submit_read) && strchr($submit_read, "<")) {
	$gallery->album->setRead($readUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_text) && strchr($submit_text, ">")) {
	$gallery->album->setChangeText($allUid, 1);
	$changed++;
} else if (isset($textUid) && isset($submit_text) && strchr($submit_text, "<")) {
	$gallery->album->setChangeText($textUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_add) && strchr($submit_add, ">")) {
	$gallery->album->setAddTo($allUid, 1);
	$changed++;
} else if (isset($addUid) && isset($submit_add) && strchr($submit_add, "<")) {
	$gallery->album->setAddTo($addUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_write) && strchr($submit_write, ">")) {
	$gallery->album->setWrite($allUid, 1);
	$changed++;
} else if (isset($writeUid) && isset($submit_write) && strchr($submit_write, "<")) {
	$gallery->album->setWrite($writeUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_delete) && strchr($submit_delete, ">")) {
	$gallery->album->setDeleteFrom($allUid, 1);
	$changed++;
} else if (isset($deleteUid) && isset($submit_delete) && strchr($submit_delete, "<")) {
	$gallery->album->setDeleteFrom($deleteUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_createSub) && strchr($submit_createSub, ">")) {
	$gallery->album->setCreateSubAlbum($allUid, 1);
	$changed++;
} else if (isset($createSubUid) && isset($submit_createSub) && strchr($submit_createSub, "<")) {
	$gallery->album->setCreateSubAlbum($createSubUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_viewFullImages) && strchr($submit_viewFullImages, ">")) {
	$gallery->album->setViewFullImages($allUid, 1);
	$changed++;
} else if (isset($viewFullImagesUid) && isset($submit_viewFullImages) && strchr($submit_viewFullImages, "<")) {
	$gallery->album->setViewFullImages($viewFullImagesUid, 0);
	$changed++;
}

if (isset($allUid) && isset($submit_addComments) && strchr($submit_addComments, ">")) {
        $gallery->album->setAddComments($allUid, 1);
        $changed++;
} else if (isset($addCommentsUid) && isset($submit_addComments) && strchr($submit_addComments, "<")) {
        $gallery->album->setAddComments($addCommentsUid, 0);
        $changed++;
}

if (isset($allUid) && isset($submit_viewComments) && strchr($submit_viewComments, ">")) {
        $gallery->album->setViewComments($allUid, 1);
        $changed++;
} else if (isset($viewCommentsUid) && isset($submit_viewComments) && strchr($submit_viewComments, "<")) {
        $gallery->album->setViewComments($viewCommentsUid, 0);
        $changed++;
}

if (isset($save) && $ownerUid) {
	$gallery->album->setOwner($ownerUid);
	$changed++;
}

if ($changed) {
	$gallery->album->save(array(i18n("Permissions have been changed")));

	if (getRequestVar('setNested')) {
		$gallery->album->setNestedPermissions();
	}
}

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $gallery->userDB->getNobody();
$ownerUid = $nobody->getUid();

$uRead = $gallery->album->getPermUids("canRead");
$uText = $gallery->album->getPermUids("canChangeText");
$uAdd = $gallery->album->getPermUids("canAddTo");
$uWrite = $gallery->album->getPermUids("canWrite");
$uDelete = $gallery->album->getPermUids("canDeleteFrom");
$uCreateSub = $gallery->album->getPermUids("canCreateSubAlbum");
$uViewFullImages = $gallery->album->getPermUids("canViewFullImages");
$uAddComments = $gallery->album->getPermUids("canAddComments");
$uViewComments = $gallery->album->getPermUids("canViewComments");

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	$uAll[$uid] = $uname;
}

$owner = $gallery->album->getOwner();
$ownerUid = $owner->getUid();

asort($uRead);
asort($uText);
asort($uWrite);
asort($uDelete);
asort($uCreateSub);
asort($uViewFullImages);
asort($uAddComments);
asort($uViewComments);
asort($uAdd);
asort($uAll);

correctPseudoUsers($uRead, $ownerUid);
correctPseudoUsers($uText, $ownerUid);
correctPseudoUsers($uWrite, $ownerUid);
correctPseudoUsers($uDelete, $ownerUid);
correctPseudoUsers($uCreateSub, $ownerUid);
correctPseudoUsers($uViewFullImages, $ownerUid);
correctPseudoUsers($uAddComments, $ownerUid);
correctPseudoUsers($uViewComments, $ownerUid);
correctPseudoUsers($uAdd, $ownerUid);

?>
<?php doctype() ?>
<html>
<head>
  <title><?php echo _("Album Permissions") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Album Permissions") ?></div>
<div class="popup" align="center">
<?php echo sprintf(_("Changing permissions for %s"), '<b>'.$gallery->album->fields["title"] . '</b>');

echo makeFormIntro("album_permissions.php", 
			array("name" => "albumperms_form"),
			array("type" => "popup"));
?>

<?php if ($gallery->user->isAdmin) { ?>
<?php echo _("Owner:") ?> <?php echo drawSelect("ownerUid", $uAll, $ownerUid, 1); ?>
<?php } ?>

<table border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td align="center">
   <?php echo drawSelect("allUid", $uAll, isset($allUid) ? $allUid : array(), 28); ?>
  </td>

  <td> &nbsp; </td>

  <td valign=top>
   <table border="0" cellspacing="3" cellpadding="0">
    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can see the album") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_read" value="-->">
      <br> <input type="submit" name="submit_read" value="<--">
     </td>
     <td align="left">
      <?php echo drawSelect("readUid", $uRead, isset($readUid) ? $readUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can change album text.") ?>
     </td>
    </tr>
    <tr>
     <td>
           <input type="submit" name="submit_text" value="-->">
      <br> <input type="submit" name="submit_text" value="<--">
     </td>
     <td>
      <?php echo drawSelect("textUid", $uText, isset($textUid) ? $textUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can add photos.") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_add" value="-->">
      <br> <input type="submit" name="submit_add" value="<--">
     </td>
     <td>
      <?php echo drawSelect("addUid", $uAdd, isset($addUid) ? $addUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
	<?php echo _("Users who can modify photos.") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_write" value="-->">
      <br> <input type="submit" name="submit_write" value="<--">
     </td>
     <td>
      <?php echo drawSelect("writeUid", $uWrite, isset($writeUid) ? $writeUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
	<?php echo _("Users who can delete photos.") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_delete" value="-->">
      <br> <input type="submit" name="submit_delete" value="<--">
     </td>
     <td>
      <?php echo drawSelect("deleteUid", $uDelete, isset($deleteUid) ? $deleteUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
	<?php echo _("Users who can create sub albums.") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_createSub" value="-->">
      <br> <input type="submit" name="submit_createSub" value="<--">
     </td>
     <td>
      <?php echo drawSelect("createSubUid", $uCreateSub, isset($createSubUid) ? $createSubUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can view full (original) images.") ?>
     </td>
    </tr>
    <tr>
     <td>   
           <input type="submit" name="submit_viewFullImages" value="-->">
      <br> <input type="submit" name="submit_viewFullImages" value="<--">
     </td>
     <td>
      <?php echo drawSelect("viewFullImagesUid", $uViewFullImages, isset($viewFullImagesUid) ? $viewFullImagesUid : array(), 3); ?>
      
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can add comments.") ?>
     </td>
    </tr>
    <tr>
     <td>
           <input type=submit name="submit_addComments" value="-->">
      <br> <input type=submit name="submit_addComments" value="<--">
     </td>
     <td>
      <?php echo drawSelect("addCommentsUid", $uAddComments, isset($addCommentsUid) ? $addCommentsUid : array(), 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan="2" class="popuptd">
      <?php echo _("Users who can view comments.") ?>
     </td>
    </tr>
    <tr>
     <td>
           <input type=submit name="submit_viewComments" value="-->">
      <br> <input type=submit name="submit_viewComments" value="<--">
     </td>
     <td>
      <?php echo drawSelect("viewCommentsUid", $uViewComments, isset($viewCommentsUid) ? $viewCommentsUid : array(), 3); ?>
     </td>
    </tr>

     </table>
  </td>
 </tr>
</table>

<label for="setNested"><?php echo _("Apply permissions to all sub-albums"); ?></label>
<input type="checkbox" id="setNested" name="setNested" value="setNested" <?php if (getRequestVar('setNested')) echo 'CHECKED'; ?>>
<br><br>
<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="button" name="done" value="<?php echo _("Done") ?>" onclick='parent.close()'>
</form>
</div>
<?php print gallery_validation_link("album_permissions.php"); ?>
</body>
</html>
