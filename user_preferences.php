<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
 */
?>
<? require_once('init.php'); ?>
<?
if (!$user->isLoggedIn()) {
	exit;	
}

if (!strcmp($submit, "Save")) {
	if (strcmp($user->getUsername(), $uname)) {
		$gErrors["uname"] = $userDB->validNewUserName($uname);
		if ($gErrors["uname"]) {
			$errorCount++;
		}
	}

	if ($old_password && !$user->isCorrectPassword($old_password)) {
		$gErrors["old_password"] = "Incorrect password";
		$errorCount++;
	}

	if ($new_password1 || $new_password2) {
		if (!$old_password) {
			$gErrors["old_password"] = "You must provide your old password to change it.";
			$errorCount++;
		}

		if (strcmp($new_password1, $new_password2)) {
			$gErrors["new_password2"] = "Passwords do not match!";
			$errorCount++;
		} else {
			$gErrors["new_password1"] = $userDB->validPassword($new_password1);
			if ($gErrors["new_password1"]) {
				$errorCount++;
			}
		}
	}

	if (!$errorCount) {
		$user->setUsername($uname);
		$user->setFullname($fullname);
		$user->setEmail($email);
		// If a new password was entered, use it.  Otherwise leave it the same.
		if ($new_password1) {
			$user->setPassword($new_password1);
		}
		$user->save();
		dismiss();
	}
}

$askForOldPassword = 1;
$uname = $user->getUsername();
$fullname = $user->getFullname();
$email = $user->getEmail();

if ($user->isAdmin()) {
	$dontChangeUsername = 1;
}

?>
<html>
<head>
  <title>Change User Preferences</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>
<span class="popuphead">Change User Preferences</span>
<br>
<br>

You can change your user information here.  If you want to change your
password, you must provide your old password and then enter the new
one twice.  You can change your username to any combination of letters
and digits.

<p>

<form name=usermodify_form method=POST>
<p>

<? include("html/userData.inc"); ?>
<p>

<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
<? if ($dontChangeUsername) { ?>
document.usermodify_form.fullname.focus();
<? } else { ?>
document.usermodify_form.uname.focus();
<? } ?>
//--> 
</script>

</body>
</html>
