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
?>
<?php
$errorCount=0;
if (!empty($action) && $action == 'create') {
	$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
	if ($gErrors["uname"]) {
		$errorCount++;
	}

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

	if (!$errorCount) {
		doctype();
		?>
	       	<html>
		<head>
		<title><?php echo _("Create User") ?></title>
		<?php common_header(); ?>
		</head>
		<body dir="<?php echo $gallery->direction ?>">
		<center>
		<?php
		$tmpUser = new Gallery_User();
		$tmpUser->setUsername($uname);
		$tmpUser->setPassword($new_password1);
		$tmpUser->setFullname($fullname);
		$tmpUser->setCanCreateAlbums($canCreate);
		$tmpUser->setEmail($email);
		$tmpUser->origEmail=$email;
		$tmpUser->setDefaultLanguage($defaultLanguage);
		$tmpUser->version = $gallery->user_version;
		$tmpUser->log("register");
		$tmpUser->save();
		print sprintf(_("User %s created"), $uname) . "<br><br>";
		if (isset($send_email)) {
		       	$msg = ereg_replace("!!PASSWORD!!", $new_password1,
				ereg_replace("!!USERNAME!!", $uname,
				       	ereg_replace("!!FULLNAME!!", $fullname,
					       	ereg_replace("!!NEWPASSWORDLINK!!", 
							$tmpUser->genRecoverPasswordHash(),
							welcome_email()))));
		       	$logmsg = sprintf(_("%s has registered by %s.  Email has been sent to %s."),
				       	$uname, $gallery->user->getUsername(), $email);
		       	$logmsg2  = sprintf("%s has registered by %s.  Email has been sent to %s.",
				       	$uname, $gallery->user->getUsername(), $email);
		       	if ($logmsg != $logmsg2) {
			       	$logmsg .= " <<<<>>>>> $logmsg2";
		       	}

			if (gallery_mail($email, _("Gallery Registration"),$msg, $logmsg)) {
			       	clearstatcache();
			       	$tmpUser->save();
			       	print sprintf(_("Email sent to %s."), $email);
			       	print "<br><br>";
		       	}
	       	} 
		?>
		<br><form><input type="submit" name="dismiss" value="<?php echo _("Dismiss") ?>"></form>
		<?php
		exit;
       	}
} else if (!empty($action) || isset($dismiss)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}
doctype();
?>
<html>
<head>
  <title><?php echo _("Create User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<p class="popuphead"><?php echo _("Create User") ?></p>

<?php
$canCreate = 1;
$canCreateChoices = array(1 => _("yes"), 0 => _("no"));

$allowChange["uname"] = true;
$allowChange["email"] = true;
$allowChange["password"] = true;
$allowChange["old_password"] = false;
$allowChange["fullname"] = true;
$allowChange["send_email"] = true;
$allowChange["create_albums"] = true;
$allowChange["default_language"] = true;
$allowChange["member_file"] = false;

?>
<div class="popup">
<?php echo _("Create a new user here.") ?>
<br>

<?php echo makeFormIntro("create_user.php", array(
				"name" => "usercreate_form", 
				"method" => "POST",
				'onsubmit' => 'usercreate_form.create.disabled = true;'));
?>
<br>

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<br>

<input type="hidden" name="action" value="">
<input type="submit" name="create" value="<?php echo _("Create") ?>" onclick="usercreate_form.action.value='create'">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>" onclick="usercreate_form.action.value='cancel'">
</form>
</div>
</center>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usercreate_form.uname.focus();
//--> 
</script>

<?php print gallery_validation_link("create_user.php"); ?>
</body>
</html>
