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
	print "Security violation\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
if (!$gallery->user->isAdmin()) {
	exit;	
}

if ($submit) {
	if (!strcmp($submit, "Save")) {
		if (strcmp($old_uname, $uname)) {
			$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
			if ($gErrors["uname"]) {
				$errorCount++;
			}
		}

		if ($new_password1 || $new_password2) {
			if (strcmp($new_password1, $new_password2)) {
				$gErrors["new_password2"] = "Passwords do not match!";
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

			header("Location: manage_users.php");
		}
	} else if (!strcmp($submit, "Cancel")) {
		header("Location: manage_users.php");
	}
}

$tmpUser = $gallery->userDB->getUserByUsername($uname);
if (!$tmpUser) {
	gallery_error("Invalid user <i>$uname</i>");
	exit;
}

if ($tmpUser->isAdmin()) {
	$dontChange["create-albums"] = 1;
}

if (!strcmp($tmpUser->getUsername(), $gallery->user->getUsername())) {
	$dontChange["admin"] = 1;
}

$fullname = $tmpUser->getFullname();
$email = $tmpUser->getEmail();

$canCreateChoices = array(1 => "yes", 0 => "no");
$canCreate = $tmpUser->canCreateAlbums() ? 1 : 0;

$isAdminChoices = array(1 => "yes", 0 => "no");
$isAdmin = $tmpUser->isAdmin() ? 1 : 0;

?>
<html>
<head>
  <title>Modify User</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead">Modify User</span>
<br>
<br>
You can change any information about the user using this form.
<p>

<?php echo makeFormIntro("modify_user.php", 
				array("name" => "usermodify_form", 
					"method" => "POST")); ?>

<input type=hidden name=old_uname value=<?php echo $uname?>>

<p>

<?php include($GALLERY_BASEDIR . "html/userData.inc"); ?>
<p>


<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel">
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//--> 
</script>

</body>
</html>
