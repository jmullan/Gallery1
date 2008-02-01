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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($save, $old_password, $new_password1, $new_password2) =
	getRequestVar(array('save', 'old_password', 'new_password1', 'new_password2'));
	
list($uname, $email, $fullname, $defaultLanguage) =
	getRequestVar(array('uname', 'email', 'fullname', 'defaultLanguage'));

if (!$gallery->user->isLoggedIn()) {
	printPopupStart(gTranslate('core', "Change User Preferences"), '', 'left');
	printInfoBox(array(array(
		'type' => 'error',
		'text' => sprintf(gTranslate('core', "You are not allowed to perform this action!. Please go back to %s."),
					galleryLink(makeGalleryUrl(), $gallery->app->galleryTitle))
	)));
	includeTemplate('overall.footer');
	exit;
}

$errorCount = 0;
if (isset($save)) {
	// security check;
	if($fullname != strip_tags($fullname)) {
		$gErrors["fullname"] =
			sprintf(gTranslate('core', "%s contained invalid data, resetting input."),
					htmlentities($fullname));
		$errorCount++;
	}

	if ($gallery->user->getUsername() != $uname) {
		if ($gallery->user->isAdmin()) {
			$gErrors['uname'] = $gallery->userDB->validNewUserName($uname);
			if ($gErrors['uname']) {
				$errorCount++;
			}
		}
		else {
			$gErrors['uname'] = gTranslate('core', "You are not allowed to change your username.");
			$errorCount++;
		}
	}

	if (!empty($old_password) && !$gallery->user->isCorrectPassword($old_password)) {
		$gErrors['old_password'] = gTranslate('core', "Password was incorrect.") ;
		$errorCount++;
	}

	if (!empty($new_password1) || !empty($new_password2)) {
		if (empty($old_password)) {
			$gErrors['old_password'] = gTranslate('core', "You must provide your old password to change it.");
			$errorCount++;
		}

		if (strcmp($new_password1, $new_password2)) {
			$gErrors['new_password2'] = gTranslate('core', "Passwords did not match!");
			$errorCount++;
		}
		else {
			$gErrors['new_password1'] = $gallery->userDB->validPassword($new_password1);
			if ($gErrors['new_password1']) {
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
			$gallery->session->language=$defaultLanguage;
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

$uname			 = $gallery->user->getUsername();
$fullname		 = $gallery->user->getFullname();
$email			 = $gallery->user->getEmail();
$defaultLanguage = $gallery->user->getDefaultLanguage();

$allowChange['uname']			 = $gallery->user->isAdmin() ? true : false;
$allowChange['email']			 = true;
$allowChange['fullname']		 = true;
$allowChange['old_password']	 = true;
$allowChange['default_language'] = true;
$allowChange['send_email']		 = false;
$allowChange['member_file']		 = false;
$allowChange['create_albums']	 = false;
$allowChange['password']		 = $gallery->user->canChangeOwnPw() ? true : false;
$allowChange['admin']			 = true;

$isAdmin = $gallery->user->isAdmin() ? 1 : 0;

printPopupStart(gTranslate('core', "Change User Preferences"), '', 'left');

if(isset($saveOK)) {
	$notice_messages[] = array(
		'type' => 'success',
		'text' => gTranslate('core', "User successfully updated.")
	);
	
	echo infoBox($notice_messages);
	
	echo "\n<br>\n";
	reload();
}

echo gTranslate('core', "You can change your user information here.");
echo gTranslate('core', "If you want to change your password, you must provide your old password and then enter the new one twice.");
echo gTranslate('core', "You can change your username to any combination of letters and digits.");

echo "\n<br>\n";

echo makeFormIntro('user_preferences.php',
	array('name' => 'usermodify_form'),
	array('type' => 'popup'));

echo "\n<br>";
include(dirname(dirname(__FILE__)) . '/layout/userData.inc');

?>
<br>
<div align="center">
	<?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
	<?php echo gButton('close', gTranslate('core', "_Close Window"), 'parent.close()'); ?>
</div>
</form>

</div>
<script type="text/javascript">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//-->
</script>

</body>
</html>
