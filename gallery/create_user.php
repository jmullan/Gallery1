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
if (!$gallery->user->isAdmin()) {
	exit;	
}
?>
<?php
$errorCount=0;
if (isset($create)) {
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
		?>
	       	<html>
		<head>
		<title><?php echo _("Create User") ?></title>
		<?php echo getStyleSheetLink() ?>
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
		$tmpUser->log("register");
		$tmpUser->save();
		print sprintf(_("User %s created"), $uname) . "<br><br>";
		if (isset($send_email)) {
		       	$msg = ereg_replace("!!PASSWORD!!", $new_password1,
				       	ereg_replace("!!USERNAME!!", $uname,
					       	ereg_replace("!!FULLNAME!!", $fullname,
						       	welcome_email())));
		       	$msg .= "\r\n\r\n" . pretty_password($new_password1, false);
		       	$logmsg = sprintf(_("%s has registered by %s.  Email has been sent to %s."),
				       	$uname, $gallery->user->getUsername(), $email);
		       	$logmsg2  = sprintf("%s has registered by %s.  Email has been sent to %s.",
				       	$uname, $gallery->user->getUsername(), $email);
		       	if ($logmsg != $logmsg2) {
			       	$logmsg .= " <<<<>>>>> $logmsg2";
		       	}

			if (gallery_mail($email, _("Gallery Registration"),$msg, $logmsg)) {
			       	print sprintf(_("Email sent to %s."), $email);
			       	print "<br><br>";
		       	}
	       	} 
		?>
		<br><form><input type="submit" name="dismiss" value="<?php echo _("Dismiss") ?>"></form>
		<?php
		exit;
       	}
} else if (isset($cancel) || isset($dismiss)) {
	header("Location: manage_users.php");
}

?>
<html>
<head>
  <title><?php echo _("Create User") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead"><?php echo _("Create User") ?></span>
<br>
<br>

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
<span class="popup">
<?php echo _("Create a new user here.") ?>
<p>


<?php echo makeFormIntro("create_user.php", array(
				"name" => "usercreate_form", 
				"method" => "POST"));
?>
<p>

<?php include($GALLERY_BASEDIR . "html/userData.inc"); ?>
<p>

<input type="submit" name="create" value="<?php echo _("Create") ?>">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>">
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.usercreate_form.uname.focus();
//--> 
</script>

</span>
</body>
</html>
