<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
if (!$gallery->user->isLoggedIn()) {
	exit;	
}

if ( isset($save)) {
	if (strcmp($gallery->user->getUsername(), $uname)) {
		$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
		if ($gErrors["uname"]) {
			$errorCount++;
		}
	}

	if ($old_password && !$gallery->user->isCorrectPassword($old_password)) {
		$gErrors["old_password"] = _("Incorrect password") ;
		$errorCount++;
	}

	if ($new_password1 || $new_password2) {
		if (!$old_password) {
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

$askForOldPassword = 1;
$uname = $gallery->user->getUsername();
$fullname = $gallery->user->getFullname();
$email = $gallery->user->getEmail();
$defaultLanguage = $gallery->user->getDefaultLanguage();

?>
<html>
<head>
  <title><?php echo _("Change User Preferences") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead"><?php echo _("Change User Preferences") ?></span>
<br>
<br>

<?php echo _("You can change your user information here.") ?>
<?php echo _("If you want to change your password, you must provide your old password and then enter the new one twice.") ?>
<?php echo _("You can change your username to any combination of letters and digits.") ?>

<p>

<?php echo makeFormIntro("user_preferences.php", array(
			"name" => "usermodify_form", 
			"method" => "POST"));
?>
<p>

<?php include($GALLERY_BASEDIR . "html/userData.inc"); ?>
<p>

<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//--> 
</script>

</body>
</html>
