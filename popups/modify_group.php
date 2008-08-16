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
require_once(dirname(dirname(__FILE__)) . '/lib/groups.php');
require_once(dirname(dirname(__FILE__)) . '/classes/Group.php');
require_once(dirname(dirname(__FILE__)) . '/classes/gallery/Group.php');

list($groupId, $save, $gname, $description, $currentUser) =
		getRequestVar(array('groupId', 'save', 'gname', 'description', 'currentUser'));

list($backToGroup, $backToUser) = getRequestVar(array('backToGroup', 'backToUser'));

if (!$gallery->user->isAdmin()) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

if(!empty($backToGroup)) {
	header("Location: " . makeGalleryHeaderUrl('manage_groups.php', array('type' => 'popup')));
}

if (!empty($backToUser)) {
	header("Location: " . makeGalleryHeaderUrl('manage_users.php', array('type' => 'popup')));
}

$notice_messages = array();

if(! empty($groupId)) {
	$group = new Gallery_Group();
	$group->load($groupId);
}
else {
	$notice_messages[] = array(
		'type' => 'error',
		'text' => gTranslate('core', "No group selected !"));

	$failure = true;
}

/**
 * User pressed "save" Button
 */
if (!empty($save) && !isset($failure)) {
	$group->setName($gname);
	$group->setDescription($description);
	$group->setMemberlist($currentUser);

	if($group->save()) {
		$notice_messages[] = array(
			'type' => 'success',
			'text' => gTranslate('core',"Group information succesfully updated.")
		);
	}
	else {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Group information was not succesfully updated!")
		);
	}
}

$groupMembers = $group->getMemberlist();

$availableUsers = array();
$currentUsers = array();

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	if ($tmpUser->isPseudo()) {
		continue;
	}

	$tmpUserName = $tmpUser->getUsername();
	if(in_array($uid, $groupMembers)) {
		$currentUsers[$uid] = $tmpUserName;
	}
	else {
	   $availableUsers[$uid] = $tmpUserName;
	}
}
asort($currentUsers);
asort($availableUsers);

printPopupStart(gTranslate('core', "Modify Group"), '', 'left');

echo infoBox($notice_messages);

echo makeFormIntro('modify_group.php',
	array('name' => 'groupmodify_form', 'onSubmit' => "checkAllOptions('currentUserBox')"),
	array('type' => 'popup', 'groupId' => $groupId));

if(! isset($failure)) {

echo gTranslate('core', "You can change any information about the group using this form.");

echo "\n<br>";

?>

<table>
	<?php echo gInput('text', 'gname', gTranslate('core',"_Name of the group"), true, $group->getName(), array('size' => 30, 'maxlength' => 25)); ?>
	<?php echo gInput('textarea', 'description', gTranslate('core',"Some _descriptive text (optional)"), true, $group->getDescription(), array('cols' => 30, 'rows' => 2)); ?>
</table>

<script type="text/javascript" src="<?php echo $gallery->app->photoAlbumURL ?>/js/selectBoxHandling.js"></script>

<table align="center" cellspacing="5">
<tr>
	<th><?php echo gTranslate('core', "Current members"); ?></th>
	<th>&nbsp;</th>
	<th><?php echo gTranslate('core', "Available user"); ?></th>
</tr>
<tr>
	<td width="33%"><?php echo drawSelect('currentUser[]', $currentUsers, '', 15, array('id' => 'currentUserBox', 'multiple' => null, 'style' => 'width: 100%')); ?></td>
	<td class="center">
	  <?php echo gButton('add', gTranslate('core', "<-- Add"), "moveSelected('availableUserBox', 'currentUserBox')"); ?>
	  <br><br>
	  <?php echo gButton('remove', gTranslate('core', "Remove -->"), "moveSelected('currentUserBox', 'availableUserBox')"); ?>
	</td>
	<td width="33%"><?php echo drawSelect('availableUser[]', $availableUsers, '', 15, array('id' => 'availableUserBox', 'multiple' => null, 'style' => 'width: 100%')); ?></td>
</tr>
</table>

<br>

<?php
}
?>
	<div class="center">
<?php

	if(! isset($failure)) {
		echo gSubmit('save', gTranslate('core', "_Save"));
		echo "\n<br>";
	}

	echo gSubmit('backToGroup', gTranslate('core', "Back to _groupmanagement"));
	if (!$GALLERY_EMBEDDED_INSIDE) {
		echo gSubmit('backToUser', gTranslate('core', "Go to _usermanagement"));
	}
?>
	</div>
</form>
</div>

<script type="text/javascript">
<!--
// position cursor in top form field
document.groupmodify_form.gname.focus();
//-->
</script>

</body>
</html>
