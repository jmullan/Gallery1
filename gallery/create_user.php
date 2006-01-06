<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

list($uname, $new_password1, $new_password2, $fullname, $email, $defaultLanguage) =
    getRequestVar(array('uname', 'new_password1', 'new_password2', 'fullname', 'email', 'defaultLanguage'));

list($formaction, $canCreate, $canChangeOwnPw, $isAdmin, $send_email, $dismiss) = 
    getRequestVar(array('formaction', 'canCreate', 'canChangeOwnPw', 'isAdmin', 'send_email', 'dismiss'));

if (!$gallery->user->isAdmin()) {
	echo _("You are not allowed to perform this action!");
	exit;	
}

$errorCount = 0;
if (!empty($formaction) && $formaction == 'create') {
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
	<div class="popup" align="center">
		<div class="popuphead"><?php echo _("Create User") ?></div>
	<?php
		$tmpUser = new Gallery_User();
		$tmpUser->setUsername($uname);
		$tmpUser->setPassword($new_password1);
		$tmpUser->setFullname($fullname);
		$tmpUser->setCanCreateAlbums($canCreate);
		$tmpUser->setCanChangeOwnPw($canChangeOwnPw);
		$tmpUser->setIsAdmin($isAdmin);
		$tmpUser->setEmail($email);
		$tmpUser->origEmail=$email;
		$tmpUser->setDefaultLanguage($defaultLanguage);
		$tmpUser->version = $gallery->user_version;
		$tmpUser->log("register");
		$tmpUser->save();

		echo infoLine(sprintf(_("User %s created"), $uname), 'success');

		if (!empty($send_email)) {
			$values = array('password' => $new_password1, 
					'username' => $uname, 
					'fullname' => $fullname, 
					'newpasswordlink' => $tmpUser->genRecoverPasswordHash());
		
			$msg = resolveWelcomeMsg($values);

			echo "\n<p><pre>". wordwrap($msg,80) ."\n</pre></p>";

		       	$logmsg = sprintf(_("New user '%s' has been registered by %s.  Gallery has sent a notification email to %s."),
				       	$uname, $gallery->user->getUsername(), $email);
		       	$logmsg2  = sprintf("New user '%s' has been registered by %s.  Gallery has sent a notification email to %s.",
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
	<br><br>
	<form>
		<input type="submit" name="moreuser" value="<?php echo _("Create another user") ?>">
		<input type="submit" name="dismiss" value="<?php echo _("Back to usermanagement") ?>">
	</form>
	</div>
</body>
</html>
		<?php
		exit;
       	}
} else if (!empty($formaction) || isset($dismiss)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}
doctype();
?>
<html>
<head>
  <title><?php echo _("Create User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Create User") ?></div>
<div class="popup" align="center">
<?php
$canCreate = 0;

$allowChange["uname"] = true;
$allowChange["email"] = true;
$allowChange["password"] = true;
$allowChange["old_password"] = false;
$allowChange["fullname"] = true;
$allowChange["send_email"] = true;
$allowChange["create_albums"] = true;
$allowChange["canChangeOwnPw"] = true;
$allowChange["default_language"] = true;
$allowChange["member_file"] = false;
$allowChange["admin"] = true;

echo _("Create a new user here.");
?>
<br>

<?php echo makeFormIntro('create_user.php', array(
        'name'     => 'usercreate_form', 
        'onsubmit' => 'usercreate_form.create.disabled = true;'));
?>
<br>

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<br>

<input type="hidden" name="formaction" value="">
<input type="submit" name="create" value="<?php echo _("Create user") ?>" onclick="usercreate_form.formaction.value='create'">
<input type="submit" name="cancel" value="<?php echo _("Back to usermanagement") ?>" onclick="usercreate_form.formaction.value='cancel'">
</form>
</div>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usercreate_form.uname.focus();
//--> 
</script>

<?php print gallery_validation_link("create_user.php"); ?>

</body>
</html>
