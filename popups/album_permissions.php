<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $gallery->userDB->getNobody();
$prev_ownerUid = $nobody->getUid();

$prev_owner = $gallery->album->getOwner();
$prev_ownerUid = $prev_owner->getUid();

if (!empty($submit)) {
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
	if (getRequestVar('setNested')) {
		$gallery->album->setNestedPermissions();
	}
}

if (!empty($save) && !empty($ownerUid) && $ownerUid != $prev_ownerUid) {
	$gallery->album->setOwner($ownerUid);

	$gallery->album->save(array(i18n("Owner has been changed")));
	if (getRequestVar('setNested')) {
		$gallery->album->setNestedPermissions();
	}
}

$owner = $gallery->album->getOwner();
$ownerUid = $owner->getUid();

$perms = array(
	'canRead',
	'canAddTo',
	'canDeleteFrom',
	'canWrite',
	'canCreateSubAlbum',
	'zipDownload',
	'canViewComments',
	'canAddComments',
	'canViewFullImages',
	'canChangeText'
);

// Set values for selectboxes
foreach($perms as $perm)  {
	$ids[$perm] = $gallery->album->getPermIds($perm);
	asort($ids[$perm]);
	correctPseudoUsers($ids[$perm], $ownerUid);
}

function userBox($perm) {
	global $ids;

	$html = '<div style="float:left;">';
	$html .= "\n\t". gSubmit("submit[$perm]", '-->') .'<br><br>';
	$html .= "\n\t" .gSubmit("submit[$perm]", '<--');
	$html .= "\n</div>";
	$html .= drawSelect("actionUid", $ids[$perm], '', 7);

	return $html;
}

$perms_detailed = array(
	'canRead'   => array(
		'type'	  => 'group',
		'initial'   => 'true',
		'title'	 => gTranslate('core', "_View album"),
		'desc'	  => gTranslate('core', "Users / Groups that can see the album."),
		'content'   => userBox('canRead')
	),
	'canAddTo'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "_Add items"),
		'desc'	  => gTranslate('core', "Users / Groups that can add items."),
		'content'   => userBox('canAddTo')
	),
	'canDeleteFrom'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "_Delete items"),
		'desc'	  => gTranslate('core', "Users / Groups that can delete items."),
		'content'   => userBox('canDeleteFrom')
	),
	'canWrite'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "_Modify items"),
		'desc'	  => gTranslate('core', "Users / Groups that can modify items."),
		'content'   => userBox('canWrite')
	),
	'canCreateSubAlbum'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "Create _Subalbums"),
		'desc'	  => gTranslate('core', "Users / Groups that can create sub albums."),
		'content'   => userBox('canCreateSubAlbum')
	),
	'zipDownload'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "Zip_download"),
		'desc'	  => gTranslate('core', "Users / Groups that can to download album (with subalbums) as archive."),
		'content'   => userBox('zipDownload')
	),
	'canViewComments'  => array(
		'type'	  => 'group',
		'name'	  => 'canViewComments',
		'title'	 => gTranslate('core', "View _comments"),
		'desc'	  => gTranslate('core', "Users / Groups that can view comments."),
		'content'   => userBox('canViewComments')
	),
	'canAddComments'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "Add c_omments"),
		'desc'	  => gTranslate('core', "Users / Groups that can add comments."),
		'content'   => userBox('canAddComments')
	),
	'canViewFullImages'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "View _full images"),
		'desc'	  => gTranslate('core', "Users / Groups that can view _full (original) images."),
		'content'   => userBox('canViewFullImages')
	),
	'canChangeText'  => array(
		'type'	  => 'group',
		'title'	 => gTranslate('core', "_Edit texts"),
		'desc'	  => gTranslate('core', "Users / Groups that can change album text."),
		'content'   => userBox('canChangeText')
	)
);

$specialUsers = array();
$users = array();
$allUsers = array();

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	if ($tmpUser->isPseudo()) {
		$specialUsers[] = array(
		   'value' => $uid,
		   'text' => "*$uname*",
		);
		$allUsers[$uid] = "*$uname*";
	}
	else {
		$users[] = array(
		   'value' => $uid,
		   'text' => $uname,
		);
		$allUsers[$uid] = $uname;
	}
}

asort($allUsers);

$groupIdList	= getGroupIdList();
$grouplist		= array();
$groups			= array();

if(! empty($groupIdList)) {
	foreach ($groupIdList as $groupID) {
		$tmpGroup = new Gallery_Group();
		$tmpGroup->load($groupID);
		$groups[] = array(
		   'value' => $groupID,
		   'text' => $tmpGroup->getName()
		);
	 }
}

array_sort_by_fields($users, 'text', 'asc', true, true);
array_sort_by_fields($groups, 'text', 'asc', true, true);

$sep1 = array(array('text' => gTranslate('core', "-- Special user --")));
$sep2 = array(array('text' => gTranslate('core', "-- User --")));
$sep3 = array(array('text' => gTranslate('core', "-- Groups --")));

$all = array_merge($sep1, $specialUsers, $sep2, $users, $sep3, $groups);

/* HTML Output Start */
printPopupStart(gTranslate('core', "Album Permissions"));

echo sprintf(gTranslate('core', "Changing permissions for: %s"), '<b>'.$gallery->album->fields["title"] . '</b>');

echo makeFormIntro('album_permissions.php',
	array('name' => 'albumperms_form'),
	array('type' => 'popup'));

if ($gallery->user->isAdmin) {
	printf(gTranslate('core', "Owner: %s"), drawSelect("ownerUid", $allUsers, $ownerUid, 1));
}

echo "\n<br><br>";
$initialtab = makeSectionTabs($perms_detailed, $initialtab, true);
echo '<input name="initialtab" id="initialtab" type="hidden" value="'. $initialtab .'">';
?>
<div class="clear"></div>
<table width="100%">
<tr>
	<td><?php echo drawSelect2('actionUid', $all, array('size' => 20)); ?></td>
	<td width="100%" style="vertical-align: top">
	<?php
	makeSimpleSectionContent($perms_detailed, $initialtab);
	?>
	<hr>
	<label for="setNested"><?php echo gTranslate('core', "Apply permissions to all sub-albums"); ?></label>
	<input type="checkbox" id="setNested" name="setNested" value="setNested" <?php if (getRequestVar('setNested')) echo 'CHECKED'; ?>>
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