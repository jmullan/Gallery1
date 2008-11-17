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

/**
 * $save	If not empty, the save button was pressed
 * $ownerUid	Possible new owner Uid
 * $submit	Array containing indicators which permission should be updated and how
 * $actionUid	Array containing a set of UIDs. This UIDs are used for the action given in $submit
 * $setNested	If not empty, permissions are set recursively
 */
list($save, $ownerUid, $submit, $actionUid, $initialtab, $setNested) =
	getRequestVar(array('save', 'ownerUid', 'submit', 'actionUid' ,'initialtab', 'setNested'));

// Hack checks
if (! isset($gallery->album)) {
	printPopupStart(gTranslate('core', "Album Permissions"));
	showInvalidReqMesg(gTranslate('core', "Invalid Request."));
	includeHtmlWrap("popup.footer");
	exit;
}

if (!$gallery->user->isAdmin() &&
	!$gallery->user->isOwnerOfAlbum($gallery->album))
{
	printPopupStart(gTranslate('core', "Album Permissions"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	includeHtmlWrap("popup.footer");
	exit;
}

$perms = array(
    'canRead'           => gTranslate('core', "Users who can see the album."),
    'canAddTo'          => gTranslate('core', "Users who can add items."),
    'canDeleteFrom'     => gTranslate('core', "Users who can delete items."),
    'canWrite'          => gTranslate('core', "Users who can modify items."),
    'canCreateSubAlbum' => gTranslate('core', "Users who can create subalbums."),
    'zipDownload'       => gTranslate('core', "Users who can download this album (with subalbums) as archive."),
    'canViewComments'   => gTranslate('core', "Users who can view comments."),
    'canAddComments'    => gTranslate('core', "Users who can add comments."),
    'canViewFullImages' => gTranslate('core', "Users who can view full (original) images."),
    'canChangeText'     => gTranslate('core', "Users who can change album text."),
);

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	if ($tmpUser->isPseudo()) {
	    $uname = "*$uname*";
	}
	$uAll[$uid] = $uname;
}

asort($uAll);

/* User pressed an arrow button */
if (!empty($submit) && is_array($submit)) {
	foreach ($submit as $perm => $action) {
		if(isset($action) && isset($actionUid)) {
			$action = unhtmlentities($action);
			if($action == '-->') {
				$gallery->album->setPerm($perm, $actionUid, true);
			}
			if($action == '<--') {
				$gallery->album->setPerm($perm, $actionUid, false);
			}
		}
	}
	$gallery->album->save(array(i18n("Permissions have been changed.")));
	if (!empty($setNested)) {
		$gallery->album->setNestedPermissions();
	}
}

/* User pressed 'save' button */
// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody		= $gallery->userDB->getNobody();
$prevOwnerUid	= $nobody->getUid();

$prevOwner	= $gallery->album->getOwner();
$prevOwnerUid	= $prevOwner->getUid();
if (!empty($save)) {
	if (!empty($ownerUid) && $ownerUid != $prevOwnerUid) {
		if($gallery->album->setOwner($ownerUid)) {
			$gallery->album->save(array(i18n("Owner has been changed.")));
			$notice_messages[] = array(
				'type' => 'success',
				'text' => gTranslate('core', "Owner has been changed."));
		}
		else {
			$notice_messages[] = array(
				'type' => 'error',
				'text' => gTranslate('core', "Owner was not changed, due to invalid User ID."));
		}
	}

	if (!empty($setNested)) {
		$gallery->album->setNestedPermissions();
		$gallery->album->save(array(i18n("Permissions set for subalbums.")));
	}
}

/* Get the current owner */
$owner		= $gallery->album->getOwner();
$ownerUid	= $owner->getUid();

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
  <title><?php echo gTranslate('core', "Album Permissions") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Album Permissions") ?></div>
<div class="popup" align="center">
<?php echo sprintf(gTranslate('core', "Changing permissions for: %s"), '<b>'.$gallery->album->fields["title"] . '</b>');

echo "\n<br><br>";

echo makeFormIntro("album_permissions.php", array("name" =>
			"albumperms_form"), array("type" => "popup"));

if ($gallery->user->isAdmin) {
    echo gTranslate('core', "Owner:") . drawSelect("ownerUid", $uAll, $ownerUid, 1, array(), true);
}
?>

<br><br>

<table border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td align="center">
   <?php echo drawSelect('actionUid', $uAll, isset($allUid) ? $allUid : array(), 28, array(), true); ?>
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
	    "\n\t<input class=\"g-button\" type=\"submit\" name=\"submit[$perm]\" value=\"-->\"><br>".
	    "\n\t<input class=\"g-button\" type=\"submit\" name=\"submit[$perm]\" value=\"<--\">"
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

<label for="setNested"><?php echo gTranslate('core', "Apply permissions to all sub-albums"); ?></label>
<input type="checkbox" id="setNested" name="setNested" value="setNested" <?php if (getRequestVar('setNested')) echo 'CHECKED'; ?>>
<br><br>
<?php echo gSubmit('save', gTranslate('core', "Save")); ?>
<?php echo gButton('done', gTranslate('core', "Done"), 'parent.close()'); ?>
</form>

<?php includeHtmlWrap("popup.footer"); ?>

</body>
</html>