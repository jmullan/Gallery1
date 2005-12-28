<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($hash, $uname, $save, $new_password1, $new_password2) = getRequestVar(array('hash', 'uname', 'save', 'new_password1', 'new_password2'));
list($fullname, $email, $defaultLanguage) = getRequestVar(array('fullname', 'email', 'defaultLanguage'));

$error_string="";
if (!isset($hash)) {
       	$error_string .= _("missing hash parameter") . "<br>";
}
if (empty($uname) ) {
       	$error_string .= _("Not a valid username") . "<br>";
} else {
       	$tmpUser = $gallery->userDB->getUserByUsername($uname);
       	if (empty($tmpUser)) {
	       	$error_string .= _("Not a valid username") . "<br>";
       	} else if (!$tmpUser->checkRecoverPasswordHash($hash)) {
	       	$error_string .= _("The recovery password is not the expected value, please try again") . "<br>";
	}
}

$errorCount=0;
if (!empty($save)) {
	if (empty($new_password1) ) {
	       	$gErrors["new_password1"] = _("You must provide your new password.");
	       	$errorCount++;
	} else if (strcmp($new_password1, $new_password2)) {
	       	$gErrors["new_password2"] = _("Passwords do not match!");
	       	$errorCount++;
       	} else {
	       	$gErrors["new_password1"] = $gallery->userDB->validPassword($new_password1);
	       	if ($gErrors["new_password1"]) {
		       	$errorCount++;
	       	}
       	}

       	if (!$error_string && !$errorCount) {
       	    $tmpUser->setFullname($fullname);
       	    $tmpUser->setEmail($email);
       	    if (isset($defaultLanguage)) {
       	        $tmpUser->setDefaultLanguage($defaultLanguage);
       	        $gallery->session->language=$defaultLanguage;
       	    }
       	    if ($new_password1) {
       	        $tmpUser->setPassword($new_password1);
       	    }
       	    $tmpUser->genRecoverPasswordHash(true);
       	    $tmpUser->log("new_password_set");
       	    $tmpUser->save();

       	    // Switch over to the new username in the session
       	    $gallery->session->username = $uname;
       	    header("Location: " . makeAlbumHeaderUrl());
       	}
}

$allowChange["uname"] = false;
$allowChange["email"] = true;
$allowChange["fullname"] = true;
$allowChange["password"] = true;
$allowChange["old_password"] = false;
$allowChange["send_email"] = false;
$allowChange["member_file"] = false;

doctype();
?>
<html>
<head>
  <title><?php echo _("Make New Password") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Make New Password") ?></div>
<div class="popup" align="center">
<?php 
if ($error_string) {
       	echo gallery_error($error_string);
       	echo "<a href='albums.php'>" . _("Enter the Gallery") . "</a></body></html>"; 
	exit;
}

echo _("You can change your user information here.");
echo _("You must enter the new password twice.");

?>

<p>

<?php 
echo makeFormIntro('new_password.php', array('name' => 'usermodify_form'));
$fullname = $tmpUser->getFullname();
$email = $tmpUser->getEmail();
$defaultLanguage = $tmpUser->getDefaultLanguage();
?>
<p>

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>
<p>
<input type="hidden" name="hash" value="<?php echo $hash ?>">
<input type="submit" name="save" value="<?php echo _("Save") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick="parent.close()">
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usermodify_form.new_password1.focus();
//--> 
</script>
</div>
</body>
</html>
