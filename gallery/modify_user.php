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
$errorCount=0;
if (isset($save)) {
	if (strcmp($old_uname, $uname)) {
		$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
		if ($gErrors["uname"]) {
			$errorCount++;
			$uname=$old_uname;
		}
	}

	if ($new_password1 || $new_password2) {
		if (strcmp($new_password1, $new_password2)) {
			$gErrors["new_password2"] = _("Passwords do not match!");
			$errorCount++;
		} else {
			$gErrors["new_password1"] = 
				$gallery->userDB->validPassword($new_password1);
			if ($gErrors["new_password1"]) {
				$errorCount++;
			}
		}
	}

	if (!$errorCount) {
		$tmpUser = $gallery->userDB->getUserByUsername($old_uname);
		$tmpUser->setUsername($uname);
		$tmpUser->setFullname($fullname);
		$tmpUser->setEmail($email);
		$tmpUser->setDefaultLanguage($defaultLanguage);
		if (isset($canCreate)) {
			$tmpUser->setCanCreateAlbums($canCreate);
		}
		if (isset($isAdmin)) {
			$tmpUser->setIsAdmin($isAdmin);
		}

		// If a new password was entered, use it.  Otherwise leave
		// it the same.
		if ($new_password1) {
			$tmpUser->setPassword($new_password1);
		}
		$tmpUser->save();
		if (!strcmp($old_uname, $gallery->session->username)) {
			$gallery->session->username = $uname;
		}

		header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
	}
} else if (isset($cancel)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}

$tmpUser = $gallery->userDB->getUserByUsername($uname);
if (!$tmpUser) {
	echo gallery_error(_("Invalid user") ." <i>$uname</i>");
	exit;
}

if ($tmpUser->isAdmin()) {
	$allowChange["create_albums"] = false;
} else {
	$allowChange["create_albums"] = true;
}

if (!strcmp($tmpUser->getUsername(), $gallery->user->getUsername())) {
	$allowChange["admin"] = true;
}

$fullname = $tmpUser->getFullname();
$email = $tmpUser->getEmail();
$defaultLanguage = $tmpUser->getDefaultLanguage();

$allowChange["uname"] = true;
$allowChange["email"] = true;
$allowChange["password"] = true;
$allowChange["fullname"] = true;
$allowChange["admin"] = true;
$allowChange["default_language"] = true;
$allowChange["send_email"] = false;
$allowChange["old_password"] = false;
$allowChange["old_password"] = false;
$allowChange["member_file"] = false;

$canCreateChoices = array(1 => _("yes"), 0 => _("no"));
$canCreate = $tmpUser->canCreateAlbums() ? 1 : 0;

$isAdminChoices = array(1 => _("yes"), 0 => _("no"));
$isAdmin = $tmpUser->isAdmin() ? 1 : 0;

?>
<?php doctype(); ?>
<html>
<head>
  <title><?php echo _("Modify User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Modify User") ?></p>

<div class="popup">
<?php echo _("You can change any information about the user using this form.") ?>

<br>

<?php echo makeFormIntro("modify_user.php", 
				array("name" => "usermodify_form", 
					"method" => "POST")); ?>

<input type="hidden" name="old_uname" value="<?php echo $uname ?>">

<br>

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<br>
<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>">
</form>
</div>
</center>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//--> 
</script>

<?php print gallery_validation_link("modify_user.php"); ?>
</body>
</html>
