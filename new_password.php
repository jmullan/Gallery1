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

require_once(dirname(__FILE__) . '/init.php');

list($hash, $uname, $save, $new_password1, $new_password2) =
	getRequestVar(array('hash', 'uname', 'save', 'new_password1', 'new_password2'));

list($fullname, $email, $defaultLanguage) =
	getRequestVar(array('fullname', 'email', 'defaultLanguage'));

$error_string = '';

if (!isset($hash)) {
	   	$error_string .= gTranslate('core', "missing hash parameter") . "<br>";
}
if (empty($uname) ) {
	   	$error_string .= gTranslate('core', "Not a valid username") . "<br>";
} else {
	   	$tmpUser = $gallery->userDB->getUserByUsername($uname);
	   	if (empty($tmpUser)) {
		   	$error_string .= gTranslate('core', "Not a valid username") . "<br>";
	   	} else if (!$tmpUser->checkRecoverPasswordHash($hash)) {
		   	$error_string .= gTranslate('core', "The recovery password is not the expected value, please try again") . "<br>";
	}
}

$errorCount = 0;
if (!empty($save)) {
	if (empty($new_password1) ) {
		   	$gErrors["new_password1"] = gTranslate('core', "You must provide your new password.");
		   	$errorCount++;
	} else if (strcmp($new_password1, $new_password2)) {
		   	$gErrors["new_password2"] = gTranslate('core', "Passwords do not match!");
		   	$errorCount++;
	   	} else {
		   	$gErrors["new_password1"] = $gallery->userDB->validPassword($new_password1);
		   	if ($gErrors["new_password1"]) {
			   	$errorCount++;
		   	}
	   	}

	// security check;
	if($fullname != strip_tags($fullname)) {
			$gErrors["fullname"] =
				sprintf(gTranslate('core', "%s contained invalid data, resetting input."), htmlentities($fullname));
			$errorCount++;
		}

	if (!empty($email) && !check_email($email)) {
		$gErrors['email'] = gTranslate('core', "You must specify a valid email address.");
		$errorCount++;
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

$allowChange['uname']		= false;
$allowChange['email']		= true;
$allowChange['fullname']	= true;
$allowChange['password']	= true;
$allowChange['old_password']	= false;
$allowChange['send_email']	= false;
$allowChange['member_file']	= false;

printPopupStart(gTranslate('core', "Make New Password"));

if (!empty($messages)) {
	echo infobox($messages);
	echo "<a href='albums.php'>" . gTranslate('core', "Enter the Gallery") . "</a></div></body></html>";
	exit;
}

echo '<div class="g-sitedesc">';
echo gTranslate('core', "You can change your user information here.");
echo gTranslate('core', "You must enter the new password twice.");

echo "\n</div>";

echo makeFormIntro('new_password.php', array('name' => 'usermodify_form'));
$fullname	= $tmpUser->getFullname();
$email		= $tmpUser->getEmail();
$defaultLanguage = $tmpUser->getDefaultLanguage();

include(dirname(__FILE__) . '/layout/userData.inc');

?>
<p>
<input type="hidden" name="hash" value="<?php echo $hash ?>">
<?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
<?php echo gButton('cancel', gTranslate('core', "_Cancel"), "location.href='". $gallery->app->photoAlbumURL ."'"); ?>
</form>

<?php includeTemplate('overall.footer'); ?>

</body>
</html>
