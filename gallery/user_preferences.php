<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

list($save, $old_password, $new_password1, $new_password2) = getRequestVar(array('save', 'old_password', 'new_password1', 'new_password2'));
list($uname, $email, $fullname, $defaultLanguage) = getRequestVar(array('uname', 'email', 'fullname', 'defaultLanguage'));

if (!$gallery->user->isLoggedIn()) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$errorCount = 0;
if (isset($save)) {
	// security check;
	if($fullname != strip_tags($fullname)) {
	    $gErrors["fullname"] =
		sprintf(gTranslate('core', "%s contained invalid data, resetting input."), htmlentities($fullname));
	    $errorCount++;
        }

	if ($gallery->user->getUsername() != $uname) {
		if ($gallery->user->isAdmin()) {
			$gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
			if ($gErrors["uname"]) {
				$errorCount++;
			}
		} else {
			$gErrors['uname'] = gTranslate('core', "You are not allowed to change your username.");
			$errorCount++;
		}
	}

	if (!empty($old_password) && !$gallery->user->isCorrectPassword($old_password)) {
		$gErrors["old_password"] = gTranslate('core', "Incorrect password.") ;
		$errorCount++;
	}

	if (!empty($new_password1) || !empty($new_password2)) {
		if (empty($old_password)) {
			$gErrors["old_password"] = gTranslate('core', "You must provide your old password to change it.");
			$errorCount++;
		}

		if (strcmp($new_password1, $new_password2)) {
			$gErrors["new_password2"] = gTranslate('core', "Passwords do not match!");
			$errorCount++;
		} else {
			$gErrors["new_password1"] = $gallery->userDB->validPassword($new_password1);
			if ($gErrors["new_password1"]) {
				$errorCount++;
			}
		}
	}

	if (!empty($email) && !check_email($email)) {
                $gErrors['email'] = gTranslate('core', "You must specify a valid email address.");
                $errorCount++;
        }

	if (!$errorCount) {
		$gallery->user->setUsername($uname);
		$gallery->user->setFullname($fullname);
		$gallery->user->setEmail($email);
		if (isset($defaultLanguage)) {
			$gallery->user->setDefaultLanguage($defaultLanguage);
			$gallery->session->language = $defaultLanguage;
		}
		// If a new password was entered, use it.  Otherwise leave it the same.
		if ($new_password1) {
 			$gallery->user->setPassword($new_password1);
		}
		$gallery->user->save();

		// Switch over to the new username in the session
		$gallery->session->username = $uname;
		$saveOK = true;
	}
}

$uname = $gallery->user->getUsername();
$fullname = $gallery->user->getFullname();
$email = $gallery->user->getEmail();
$defaultLanguage = $gallery->user->getDefaultLanguage();

$allowChange["uname"]			= $gallery->user->isAdmin() ? true : false;
$allowChange["email"]			= true;
$allowChange["fullname"]		= true;
$allowChange["old_password"]	= true;
$allowChange["default_language"]= true;
$allowChange["send_email"]		= false;
$allowChange["member_file"]		= false;
$allowChange["create_albums"]	= false;
$allowChange["password"]		= $gallery->user->canChangeOwnPw() ? true : false;
$allowChange["admin"]			= true;

$isAdmin = $gallery->user->isAdmin() ? 1 : 0;

doctype();

printPopupStart(gTranslate('core', "Change User Preferences"), gTranslate('core', "Change User Preferences"), langLeft());

if(isset($saveOK)) {
    echo infoLine(gTranslate('core', "User successfully updated."), 'success');
    echo "\n<br>\n";
    echo '<script language="JavaScript" type="text/javascript">opener.location.reload()</script>';
}

echo gTranslate('core', "You can change your user information here.") . '  ' .
	 gTranslate('core', "If you want to change your password, you must provide your old password and then enter the new one twice.") . '  ' .
	 gTranslate('core', "You can change your username to any combination of letters and digits.");

echo "\n<br>\n";

echo makeFormIntro('user_preferences.php', array('name' => 'usermodify_form'));

echo "\n<br>";
include(dirname(__FILE__) . '/html/userData.inc');

?>
<br>
<div align="center">
	<input type="submit" name="save" value="<?php echo gTranslate('core', "Save") ?>">
	<input type="button" name="close" value="<?php echo gTranslate('core', "Close Window") ?>" onclick="parent.close()">
</div>
</form>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//-->
</script>
</div>
<?php print gallery_validation_link("user_preferences.php"); ?>

</body>
</html>
