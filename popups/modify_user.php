<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($save, $dismiss) =
	getRequestVar(array('save', 'dismiss'));

list($old_uname, $uname, $new_password1, $new_password2, $fullname) =
	getRequestVar(array('old_uname', 'uname', 'new_password1', 'new_password2', 'fullname'));

list($email, $defaultLanguage, $canCreate, $canChangeOwnPw, $isAdmin) =
	getRequestVar(array('email', 'defaultLanguage', 'canCreate','canChangeOwnPw', 'isAdmin'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Modify User"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$notice_messages = array();
$gErrors = array();

/**
 * User pressed "save" Button
 * If (changed) user name is valid and password match,
 * then load former user as temp user and overwrite with new values
 * If one modified itself, changes current user.
 */
if (!empty($save)) {
	if ($old_uname != $uname) {
		$gErrors['uname'] = $gallery->userDB->validNewUserName($uname);
		if ($gErrors['uname']) {
			$failure = true;
			$uname = $old_uname;
		}
	}

	if ($new_password1 || $new_password2) {
		if (strcmp($new_password1, $new_password2)) {
			$gErrors['new_password2'] = gTranslate('core', "Passwords do not match!");
			$failure = true;
		}
		else {
			$gErrors['new_password1'] = $gallery->userDB->validPassword($new_password1);
			if ($gErrors['new_password1']) {
				$failure = true;
			}
		}
	}

	if (!isset($failure)) {
		$tmpUser = $gallery->userDB->getUserByUsername($old_uname);
		$tmpUser->setUsername($uname);
		$tmpUser->setFullname($fullname);
		$tmpUser->setEmail($email);
		$tmpUser->setDefaultLanguage($defaultLanguage);
		$tmpUser->setCanCreateAlbums($canCreate);
		$tmpUser->setCanChangeOwnPw($canChangeOwnPw);
		$tmpUser->setIsAdmin($isAdmin);

		// If a new password was entered, use it.  Otherwise leave
		// it the same.
		if ($new_password1) {
			$tmpUser->setPassword($new_password1);
		}

		$tmpUser->save();
		if (!strcmp($old_uname, $gallery->session->username)) {
			$gallery->session->username = $uname;
		}

		$notice_messages[] = array(
			'type' => 'success',
			'text' => gTranslate('core',"User information succesfully updated.")
		);
	}
	else {
		$notice_messages[] = array(
			'type' => 'error',
			'text' => gTranslate('core',"User information was not succesfully updated!")
		);
	}
}
else if (isset($dismiss)) {
	header('Location: ' . makeGalleryHeaderUrl('manage_users.php', array('type' => 'popup')));
}

$tmpUser = $gallery->userDB->getUserByUsername($uname);

if (!$tmpUser) {
	echo gallery_error(gTranslate('core', "Invalid user") ." <i>$uname</i>");
	exit;
}

if ($tmpUser->isAdmin()) {
	$allowChange['create_albums'] =  false;
	$allowChange['canChangeOwnPw'] = false;
}
else {
	$allowChange['create_albums'] =  true;
	$allowChange['canChangeOwnPw'] = true;
}

if (!strcmp($tmpUser->getUsername(), $gallery->user->getUsername())) {
	$allowChange['admin'] = true;
}

$fullname	= $tmpUser->getFullname();
$email		= $tmpUser->getEmail();
$defaultLanguage = $tmpUser->getDefaultLanguage();

$allowChange['uname'] =			true;
$allowChange['email'] =			true;
$allowChange['fullname'] =		true;
$allowChange['admin'] =			true;
$allowChange['default_language'] =	true;
$allowChange['send_email'] =		false;
$allowChange['member_file'] =		false;
$allowChange['password'] =		true;
$allowChange['old_password'] =		false;

$canCreate = $tmpUser->canCreateAlbums() ? 1 : 0;
$isAdmin = $tmpUser->isAdmin() ? 1 : 0;
$canChangeOwnPw = $tmpUser->canChangeOwnPw() ? 1: 0;

printPopupStart(gTranslate('core', "Modify User"), '', 'left');

echo infoBox($notice_messages);

echo gTranslate('core', "You can change any information about the user using this form.");

echo "\n<br>";

echo makeFormIntro('modify_user.php',
	array('name' => 'usermodify_form'),
	array('old_uname' => $uname, 'type' => 'popup')
);
?>

<br>

<?php include(dirname(dirname(__FILE__)) . '/layout/userData.inc'); ?>

<div align="center">
<?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
<?php echo gSubmit('dismiss', gTranslate('core', "_Back to usermanagement")); ?>
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
