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

list($save, $ownerUid, $submit, $actionUid) =
	getRequestVar(array('save', 'ownerUid', 'submit', 'actionUid'));

// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

$perms = array(
    'canRead'		=> _("Users who can see the album."),
    'canAddTo'		=> _("Users who can add photos."),
    'canDeleteFrom'	=> _("Users who can delete photos."),
    'canWrite'		=> _("Users who can modify photos."),
    'canCreateSubAlbum' => _("Users who can create sub albums."),
    'zipDownload' 	=> _("Users who can to download album (with subalbums) as archive."),
    'canViewComments' 	=> _("Users who can view comments."),
    'canAddComments'	=> _("Users who can add comments."),
    'canViewFullImages'	=> _("Users who can view full (original) images."),
    'canChangeText' 	=> _("Users who can change album text."),
);

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	$uAll[$uid] = $uname;
}

$changed = 0;
if(empty($submit)) {
    $submit = array();
}

foreach ($submit as $perm => $action) {
    if(isset($action) && isset($actionUid)) {
        if($action == '-->') {
	    $gallery->album->setPerm($perm, $actionUid, true);
	    $changed++;	
        }
	if($action == '<--') {
	    $gallery->album->setPerm($perm, $actionUid, false);
	    $changed++;
	}
    }
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

$owner = $gallery->album->getOwner();
$ownerUid = $owner->getUid();

// Set values for selectboxes
foreach($perms as $perm => $trash)  {
    $uids[$perm] = $gallery->album->getPermUids($perm);
    asort($uids[$perm]);
    correctPseudoUsers($uids[$perm], $ownerUid);
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Album Permissions") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Album Permissions") ?></div>
<div class="popup" align="center">
<?php echo sprintf(_("Changing permissions for %s"), '<b>'.$gallery->album->fields["title"] . '</b>');

echo makeFormIntro("album_permissions.php", array("name" =>
			"albumperms_form"), array("type" => "popup"));

if ($gallery->user->isAdmin) {
    echo _("Owner:") . drawSelect("ownerUid", $uAll, $ownerUid, 1);
}
?>

<table border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td align="center">
   <?php echo drawSelect('actionUid', $uAll, isset($allUid) ? $allUid : array(), 28); ?>
  </td>

  <td>&nbsp;</td>

  <td style="vertical-align: top">
<?php

$permsTable = new galleryTable();
$permsTable->setColumnCount(2);
foreach($perms as $perm => $permDesc) {
    $permsTable->addElement(array('content' => $permDesc, 'cellArgs' => array('colspan' => 2)));
    $permsTable->addElement(
	array('content' => 
	    "\n\t<input type=\"submit\" name=\"submit[$perm]\" value=\"-->\"><br>".
	    "\n\t<input type=\"submit\" name=\"submit[$perm]\" value=\"<--\">"
	));
    $permsTable->addElement(
	array('content' => drawSelect("actionUid", $uids[$perm], '', 3, array(), true))
    );
}
echo $permsTable->render();
?>    
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
