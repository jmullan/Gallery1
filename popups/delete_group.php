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

list($gnames, $deleteGroup, $backToUsers, $backToGroups) =
	getRequestVar(array('gnames', 'deleteGroup', 'backToUsers', 'backToGroups'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Delete Gallery usergroup"), '', 'left');
	showInvalidReqMesg(echo gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (!empty($backToGroups)) {
	header('Location: '. makeGalleryHeaderUrl("manage_groups.php", array('type' => 'popup')));
}

if (!empty($backToUsers)) {
	header('Location: '. makeGalleryHeaderUrl("manage_users.php", array('type' => 'popup')));
}

$messages = array();
$deleted = 0;
$failed = 0;

if (!empty($deleteGroup) && !empty($gnames)) {
	foreach($gnames as $nr => $groupId) {
		$status = deleteGroup($groupId);
		if($status) {
			$deleted++;
		}
		else {
			$failed++;
		}
		unset($gnames[$nr]);
	}

	if($deleted > 0) {
		$messages[] = array(
			'type' => 'success',
			'text' => gTranslate('core',
				"Successfully deleted %d group.",
				"Successfully deleted %d groups.", $deleted, '', true)
		);
	}
	else {
		$messages[] = array(
			'type' => 'error',
			'text' => sprintf(gTranslate('core', "%d not successfully deleted."), $failed)
		);
	}
}
elseif (!empty($deleteGroup) && empty($gnames)) {
	$messages[] = array(
		'type' => 'warning',
		'text' => sprintf(gTranslate('core', "No groups deleted."), $failed)
	);
}

printPopupStart(gTranslate('core', "Delete Gallery usergroup"), '', 'left');

if (! empty($gnames)) {
	echo "\n<p>". gTranslate('core', "Groups can have special permissions in each album.") . '<br>' .
	gTranslate('core', "If you delete this group, any such permissions go away.", "If you delete these groups, any permissions will go away.", sizeof($gnames)) .
	gTranslate('core', "Deleted groups cannot be recovered.") .
	gTranslate('core', "Even if this group is recreated, those permissions are gone.", "Even if you recreate one of those groups, the permissions are gone.", sizeof($gnames));
}

echo infoBox($messages);

echo "\n<center>";
echo makeFormIntro('delete_group.php',
	array('name' => 'deletegroup_form',
		  'onsubmit' => "deletegroup_form.deleteButton.disabled='true'"),
	array('type' => 'popup')
);

if (! empty($gnames)) {
	echo gTranslate('core', "Do you really want to delete group:", "Do you really want to delete these groups:", sizeof($gnames));
	echo "\n<table>\n";
	foreach ($gnames as $gid) {
		$tmpGroup = new Gallery_Group();
		$tmpGroup->load($gid);
		echo gInput('checkbox', 'gnames[]', $tmpGroup->getName(), true, $gid, array('checked' => null));
	}
	echo "</table>";
?>

<br><br>
<?php
	echo gSubmit('deleteGroup', gTranslate('core', "_Delete"));
}
elseif(empty($messages)) {
	echo gTranslate('core', "No groups selected for deletion.");
	echo "<br>";
}

echo gInput('hidden', 'formaction', null ,false, '');
echo gSubmit('backToGroups', gTranslate('core', "Back to _group management"));
if (!$GALLERY_EMBEDDED_INSIDE) {
	echo gSubmit('backToUser', gTranslate('core', "Go to _usermanagement"));
}
?>
</form>
</center>
</div>

</body>
</html>
