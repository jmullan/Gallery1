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

require(dirname(__FILE__) . '/init.php');

if (!$gallery->user->isAdmin()) {
	echo _("You are no allowed to perform this action !");
	exit;	
}

if (isset($action) && $action == 'delete') {
	$gallery->userDB->deleteUserByUsername($uname);
}
if (!empty($action)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}

doctype();
?>
<html>
<head>
  <title><?php echo _("Delete User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Delete User") ?></p>

<div class="popup">
<?php echo makeFormIntro("delete_user.php", array('name' => 'deleteuser_form', 
						'onsubmit' => 'deleteuser_form.delete.disabled = true;')); ?>
<input type="hidden" name="uname" value="<?php echo $uname ?>">

<?php
if (!strcmp($gallery->user->getUsername(), $uname)) {
	echo '<p align="center">';
	echo gallery_error(_("You can't delete your own account!"));
	echo '</p>';
} else {
	echo _("Users can have special permissions in each album.") .
		_("If you delete this user, any such permissions go away.") .
		_("Users cannot be recreated.") .
		_("Even if this user is recreated, those permissions are gone.");
?>
<p><b><?php echo  _("Do you really want to delete user"). ": ". $uname ?><b></p>

<input type="hidden" name="action" value="">
<input type="submit" name="delete" value="<?php echo _("Delete") ?>" onclick="deleteuser_form.action.value='delete'">
<?php
}
?>

<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>" onclick="deleteuser_form.action.value='cancel'">
</form> 

</div>
</center>
<?php print gallery_validation_link("delete_user.php"); ?>
</body>
</html>
