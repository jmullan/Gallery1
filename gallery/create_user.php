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
	if (!strcmp(htmlentities($submit), _("Create"))) {
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
			$tmpUser = new Gallery_User();
			$tmpUser->setUsername($uname);
			$tmpUser->setPassword($new_password1);
			$tmpUser->setFullname($fullname);
			$tmpUser->setCanCreateAlbums($canCreate);
			$tmpUser->setEmail($email);
			$tmpUser->save();
			header("Location: manage_users.php");
		}
	} else if (!strcmp($submit, _("Cancel"))) {
		header("Location: manage_users.php");
	}
}

$canCreateChoices = array(1 => _("yes"), 0 => _("no"));
$canCreate = 1;

?>
<html>
<head>
  <title><?php echo _("Create User") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<center>
<span class="popuphead"><?php echo _("Create User") ?></span>
<br>
<br>
<?php echo _("Create a new user here.") ?>
<p>


<?php echo makeFormIntro("create_user.php", 
			array("name" => "usercreate_form", 
				"method" => "POST")); ?>
<p>

<?php include($GALLERY_BASEDIR . "html/userData.inc"); ?>
<p>

<input type=submit name="submit" value=<?php echo '"'. _("Create") .'"' ?>>
<input type=submit name="submit" value=<?php echo '"'. _("Cancel") .'"' ?>>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.usercreate_form.uname.focus();
//--> 
</script>

</body>
</html>
