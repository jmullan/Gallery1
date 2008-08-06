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

list($formaction, $unames) = getRequestVar(array('formaction', 'unames'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Delete User"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (isset($formaction) && $formaction == 'delete') {
	foreach($unames as $user) {
		$gallery->userDB->deleteUserByUsername($user);
	}
}

if (!empty($formaction)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}

doctype();

$error = NULL;
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Delete user") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Delete user") ?></div>
<div class="popup" align="center">

<?php echo makeFormIntro("delete_user.php", array(
                                "name" => "deleteuser_form"));
//				"onsubmit" => "deleteuser_form.deleteButton.disabled='true'"));

foreach ($unames as $user) {
	if (!strcmp($gallery->user->getUsername(), $user)) {
		echo '<p align="center">';
		echo infoLine(gallery_error(gTranslate('core', "You can't delete your own account!")),'error');
		echo '</p>';
		$error++;
	}
}
if (! isset($error)) {
	echo gTranslate('core', "Users can have special permissions in each album.") .
	gTranslate('core', "If you delete this user, any such permissions go away.", "If you delete these users, any permissions will go away.", sizeof($unames)) .
	gTranslate('core', "Deleted users cannot be recovered.") .
	gTranslate('core', "Even if this user is recreated, those permissions are gone.", "Even if you recreate one of those users, the permissions are gone.", sizeof($unames));

	echo "\n<p>" . gTranslate('core', "Do you really want to delete user:", "Do you really want to delete these users:", sizeof($unames));
	foreach ($unames as $key => $value) {
		echo "<input type=\"hidden\" name=\"unames[$key]\" value=\"$value\"><br>$value\n";
	}
?>
<br><br>
<input type="submit" name="deleteButton" value="<?php echo gTranslate('core', "Delete") ?>" onclick="deleteuser_form.formaction.value='delete'">
<?php
}
?>
<input type="hidden" name="formaction" value="">
<input type="submit" name="cancel" value="<?php echo gTranslate('core', "Cancel") ?>" onclick="deleteuser_form.formaction.value='cancel'">
</form>
</div>

<?php print gallery_validation_link("delete_user.php"); ?>
</body>
</html>
