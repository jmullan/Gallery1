<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

list($formaction, $unames) = getRequestVar(array('formaction', 'unames'));

if (!$gallery->user->isAdmin()) {
	echo _("You are not allowed to perform this action!");
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
?>
<html>
<head>
  <title><?php echo _("Delete User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Delete User") ?></div>
<div class="popup" align="center">

<?php echo makeFormIntro("delete_user.php", array(
                                "name" => "deleteuser_form"));
//				"onsubmit" => "deleteuser_form.deleteButton.disabled='true'"));

foreach ($unames as $user) {
	if (!strcmp($gallery->user->getUsername(), $user)) {
		echo '<p align="center">';
		echo gallery_error(_("You can't delete your own account!"));
		echo '</p>';
		$error++;
	}
}
if (! isset($error)) {	
	echo _("Users can have special permissions in each album.") .
	ngettext("If you delete this user, any such permissions go away.", "if you delete these users, any permissions will go away", sizeof($unames)) .
	_("Users cannot be recreated.") .
	ngettext ("Even if this user is recreated, those permissions are gone.", "Even if you recreate one of those users, the permissions are gone.", sizeof($unames));
?>
<p><b><?php echo ngettext("Do you really want to delete user", "Do you really want to delete these users", sizeof($unames)); ?>:
<?php 
	foreach ($unames as $key => $value) { 
		echo "<input type=\"hidden\" name=\"unames[$key]\" value=\"$value\"><br>$value\n";
	}
?>
<br><br>
<input type="submit" name="deleteButton" value="<?php echo _("Delete") ?>" onclick="deleteuser_form.formaction.value='delete'">
<?php
}
?>
<input type="hidden" name="formaction" value="">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>" onclick="deleteuser_form.formaction.value='cancel'">
</form> 
<?php print gallery_validation_link("delete_user.php"); ?>
</div>
</body>
</html>
