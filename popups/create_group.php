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

list($createGroup, $gname, $description) =
	getRequestVar(array('createGroup', 'gname', 'description'));

list($backToGroup, $backToUser) =
	getRequestVar(array('backToGroup', 'backToUser'));

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

$messages = array();

printPopupStart(gTranslate('core', "Create Gallery Group"), '', 'left');

if (!empty($createGroup)) {
	$gNameError = validNewGroupName($gname);

	if($gNameError) {
		$messages[] = array('type' => 'error', 'text' => $gNameError);
	}
	else {
		$tmpGroup = new Gallery_Group();
		$tmpGroup->setName($gname);
		$tmpGroup->setDescription($description);
		$tmpGroup->save();

		$messages[] = array(
			'type' => 'success',
			'text' => sprintf(gTranslate('core', "Group '%s' created.<br>You can now create another group if you want."), $gname)
		);
	}
}

echo "\n<div class=\"center\">". gTranslate('core', "Create a new Gallery usergroup here.") .'</div>';

echo infoBox($messages);

echo makeFormIntro('create_group.php',
	array('name' => 'groupcreate_form', 'onSubmit' => 'groupcreate_form.create.disabled = true;'),
	array('type' => 'popup'));
?>

<table>
<tr>
	<td><?php echo gTranslate('core',"Name of the group"); ?></td>
	<td><input type="text" name="gname" size="30" maxlength="25"></td>
</tr>
<tr>
	<td><?php echo gTranslate('core',"Some descriptive text (optional)"); ?></td>
	<td><textarea name="description" cols="30" rows="2"></textarea></td>
</tr>
</table>

<br>

<div class="center">
<?php
	echo gSubmit('createGroup', gTranslate('core', "_Create group"));
	echo gSubmit('backToGroup', gTranslate('core', "Back to groupmanagement"));
	if (!$GALLERY_EMBEDDED_INSIDE) {
		echo gSubmit('backToUser', gTranslate('core', "Go to _usermanagement"));
	}
?>

</div>
</form>
</div>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.groupcreate_form.gname.focus();
//-->
</script>

</body>
</html>
