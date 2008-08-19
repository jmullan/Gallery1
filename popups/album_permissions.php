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

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/lib/tabs.php');
require_once(dirname(dirname(__FILE__)) . '/lib/users.php');
require_once(dirname(dirname(__FILE__)) . '/lib/groups.php');
require_once(dirname(dirname(__FILE__)) . '/classes/Group.php');
require_once(dirname(dirname(__FILE__)) . '/classes/gallery/Group.php');

$notice_messages	= array();
$global_notice_messages	= array();

/**
 * $save	If not empty, the save button was pressed
 * $ownerUid	Possible new owner Uid
 * $submit	Array containing indicators which permission should be updated and how
 * $actionUids	Array containing a set of UIDs. This UIDs are used for the action given in $submit
 * $setNested	If not empty, permissions are set recursively
 */

list($save, $ownerUid, $submit, $actionUids, $initialtab, $setNested) =
	getRequestVar(array('save', 'ownerUid', 'submit', 'actionUids' ,'initialtab', 'setNested'));

// Hack checks
if (! isset($gallery->album)) {
	printPopupStart(gTranslate('core', "Album Permissions"));
	showInvalidReqMesg(gTranslate('core', "Invalid Request."));
	exit;
}

if (!$gallery->user->isAdmin() &&
	!$gallery->user->isOwnerOfAlbum($gallery->album))
{
	printPopupStart(gTranslate('core', "Album Permissions"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

/* User pressed an arrow button */
if (!empty($submit) && is_array($submit)) {
	foreach ($submit as $perm => $action) {
		if(isset($action) && isset($actionUids)) {
			$action = unhtmlentities($action);
			if($action == '-->') {
				$gallery->album->setPerm($perm, $actionUids, true);
			}
			if($action == '<--') {
				$gallery->album->setPerm($perm, $actionUids, false);
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

// $perms & $permsDetailed
// Require below must be after getting the owner !
// Reason is that $permsDetailed depends on it.
require_once(dirname(dirname(__FILE__)) . '/includes/definitions/albumPermissions.php');

list($specialUsers, $users, $allUsers) = buildUsersList();
$groupList = buildGroupsList();

$sep1 = array(array('text' => gTranslate('core', "-- Special user --")));
$sep2 = array(array('text' => gTranslate('core', "-- User --")));
$sep3 = array(array('text' => gTranslate('core', "-- Groups --")));

$all = array_merge($sep1, $specialUsers, $sep2, $users, $sep3, $groupList);

$notice_messages = array_merge($notice_messages, $global_notice_messages);

/* Real HTML Output Start */
printPopupStart(sprintf(gTranslate('core', "Album Permissions :: '%s'"),
		 			'<b>'.$gallery->album->fields["title"] . '</b>'));

printInfoBox($notice_messages);

echo makeFormIntro('album_permissions.php',
	array('name' => 'albumperms_form'),
	array('type' => 'popup'));

if ($gallery->user->isAdmin) {
	printf(gTranslate('core', "Owner: %s"), drawSelect("ownerUid", $allUsers, $ownerUid, 1));
}

echo "\n<br><br>";
$initialtab = makeSectionTabs($permsDetailed, $initialtab, true);
echo gInput('hidden', 'initialtab', null, false, $initialtab);
?>
	<div class="clear"></div>
	<table width="100%">
	<tr>
		<td><?php echo drawSelect2('actionUids', $all, array('size' => 20)); ?></td>
		<td width="100%" style="vertical-align: top">
		<?php
		makeSimpleSectionContent($permsDetailed, $initialtab);
		?>
		<hr>
		<label for="setNested"><?php echo gTranslate('core', "Apply permissions to all sub-albums"); ?></label>
		<input type="checkbox" id="setNested" name="setNested" value="setNested" <?php if (!empty($setNested)) echo 'CHECKED'; ?>>
		<br><br>
		<?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
		<?php echo gButton('done', gTranslate('core', "_Done"), 'parent.close()'); ?>
		</td>
	</tr>
	</table>
</form>

<?php
	includeTemplate('overall.footer');
?>
</body>
</html>