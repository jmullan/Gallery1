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

if (!$gallery->user->isLoggedIn()) {
	echo _("You are no allowed to perform this action !");
	exit;	
}

$errorCount=0;
if ( isset($save)) {
	if (strcmp($gallery->user->getUsername(), $uname)) {
		$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
		if ($gErrors["uname"]) {
			$errorCount++;
		}
	}

	if (!empty($old_password) && !$gallery->user->isCorrectPassword($old_password)) {
		$gErrors["old_password"] = _("Incorrect password") ;
		$errorCount++;
	}

	if (!empty($new_password1) || !empty($new_password2)) {
		if (empty($old_password)) {
			$gErrors["old_password"] = _("You must provide your old password to change it.");
			$errorCount++;
		}

		if (strcmp($new_password1, $new_password2)) {
			$gErrors["new_password2"] = _("Passwords do not match!");
			$errorCount++;
		} else {
			$gErrors["new_password1"] = $gallery->userDB->validPassword($new_password1);
			if ($gErrors["new_password1"]) {
				$errorCount++;
			}
		}
	}

	if (!$errorCount) {
		$gallery->user->setUsername($uname);
		$gallery->user->setFullname($fullname);
		$gallery->user->setEmail($email);
		if (isset($defaultLanguage)) {
			$gallery->user->setDefaultLanguage($defaultLanguage);
			$gallery->session->language=$defaultLanguage;
		}
		// If a new password was entered, use it.  Otherwise leave it the same.
		if ($new_password1) {
 			$gallery->user->setPassword($new_password1);
		}
		$gallery->user->save();

		// Switch over to the new username in the session
		$gallery->session->username = $uname;
		dismissAndReload();
	}
}

$uname = $gallery->user->getUsername();
$fullname = $gallery->user->getFullname();
$email = $gallery->user->getEmail();
$defaultLanguage = $gallery->user->getDefaultLanguage();

$allowChange["uname"] = true;
$allowChange["email"] = true;
$allowChange["fullname"] = true;
$allowChange["password"] = true;
$allowChange["old_password"] = true;
$allowChange["default_language"] = true;
$allowChange["send_email"] = false;
$allowChange["member_file"] = false;
$allowChange["create_albums"] = false;

doctype();
?>
<html>
<head>
  <title><?php echo _("Change User Preferences") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Change User Preferences") ?></p>

<?php echo _("You can change your user information here.") ?>
<?php echo _("If you want to change your password, you must provide your old password and then enter the new one twice.") ?>
<?php echo _("You can change your username to any combination of letters and digits.") ?>

<br>

<?php echo makeFormIntro("user_preferences.php", array(
			"name" => "usermodify_form", 
			"method" => "POST"));
?>

<br>
<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>
<br>

<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick="parent.close()">
</form>

</center>
<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//--> 
</script>
<?php print gallery_validation_link("user_preferences.php"); ?>
</body>
</html>
