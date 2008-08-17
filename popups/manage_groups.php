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

list($create, $bulk_create, $modify, $delete, $gnames) =
	getRequestVar(array('create', 'bulk_create', 'modify', 'delete', 'gnames'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Manage Groups"), '', 'left');
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$notice_messages = array();

if (!empty($create)) {
	header('Location: ' . makeGalleryHeaderUrl('create_group.php', array('type' => 'popup')));
}

if ( (isset($modify) || isset($delete)) && ! isset($gnames)) {
	$notice_messages[] = array('type' => 'error', 'text' => gTranslate('core', "Please select a group"));
}
elseif (isset($modify)) {
	header('Location: ' . makeGalleryHeaderUrl('modify_group.php', array('groupId' => $gnames[0], 'type' => 'popup')));
}
elseif (isset($delete)) {
	header('Location: ' . makeGalleryHeaderUrl('delete_group.php', array('gnames' => $gnames, 'type' => 'popup')));
}

require_once(dirname(dirname(__FILE__)) .'/lib/groups.php');
require_once(dirname(dirname(__FILE__)) .'/classes/Group.php');
require_once(dirname(dirname(__FILE__)) .'/classes/gallery/Group.php');

$groupIdList = getGroupIdList();
$grouplist = array();

if(! empty($groupIdList)) {
	foreach ($groupIdList as $groupID) {
		$tmpGroup = new Gallery_Group();
		$tmpGroup->load($groupID);
		$grouplist[$groupID] = $tmpGroup->getName();
	 }
}

asort($grouplist);

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Manage Groups"); ?></title>
  <?php common_header(); ?>
</head>
<body class="g-popup" onload="enableButtons()">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo gTranslate('core', "Manage Gallery usergroups"); ?></div>
</div>
<div class="g-content-popup center">

<?php
echo infoBox($notice_messages);

echo gTranslate('core', "You can create, modify and delete Gallery usergroups here.");

echo makeFormIntro('manage_groups.php', array(), array('type' => 'popup'));

if (empty($grouplist)) {
	echo '<div class="g-sitedesc">' . gTranslate('core', 'No groups found.') . '</div>';
}
else {
	echo drawSelect('gnames[]', $grouplist, '', 15,
	   array('id' => 'groupNameBox',
			 'onChange' => 'enableButtons()',
			 'multiple' => null)
	);

	echo "\n<br>";
	echo gTranslate('core', "To select multiple groups for deletion, hold down the Control (PC) or Command (Mac) key while clicking.");

}
echo "\n<br><br>";

echo gSubmit('create', gTranslate('core', "Create _new group"));

if (!empty($grouplist)) {
	echo gSubmit('modify', gTranslate('core', "_Modify"));
	echo gSubmit('delete', gTranslate('core', "_Delete"));
}
echo gButton('done', gTranslate('core', "_Done"), 'parent.close()');
?>
</form>

</div>

<script type="text/javascript">
	var groupNameBox = document.getElementById('groupNameBox');
	var groupCount = groupNameBox.length;

	var createButton = document.getElementById('create');
	var modifyButton = document.getElementById('modify');
	var deleteButton = document.getElementById('delete');
	var doneButton   = document.getElementById('done');

	function enableButtons() {
		var selected = 0;
		for (i = 0; i < groupCount; i++) {
			if(groupNameBox.options[i].selected) {
				selected++;
			}
		}

		if(selected == 0) {
			modifyButton.disabled = true;
			modifyButton.className = 'g-buttonDisable';
			deleteButton.disabled = true;
			deleteButton.className = 'g-buttonDisable';

		}
		else if (selected > 1) {
			modifyButton.disabled = true;
			modifyButton.className = 'g-buttonDisable';
		}
		else {
			modifyButton.disabled = false;
			modifyButton.className = 'g-button';
			deleteButton.disabled = false;
			deleteButton.className = 'g-button';
		}

	}
</script>
</body>
</html>
