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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($save, $old_uname, $uname, $new_password1, $new_password2, $fullname, $dismiss) = 
    getRequestVar(array('save', 'old_uname', 'uname', 'new_password1', 'new_password2', 'fullname', 'dismiss'));

list($email, $defaultLanguage, $canCreate, $canChangeOwnPw, $isAdmin) = 
    getRequestVar(array('email', 'defaultLanguage', 'canCreate','canChangeOwnPw', 'isAdmin'));

if (!$gallery->user->isAdmin()) {
    echo gTranslate('core', "You are not allowed to perform this action!");
    exit;	
}

$errorCount = 0;
$msg = '';
$infoLineType = '';

/**
 * User pressed "save" Button
 * If (changed) user name is valid and password match,
 * then load former user as temp user and overwrite with new values
 * If one modified itself, changes current user.
 */
if (!empty($save)) {
    if (strcmp($old_uname, $uname)) {
        $gErrors["uname"] = $gallery->userDB->validNewUserName($uname);
        if ($gErrors["uname"]) {
            $errorCount++;
            $uname = $old_uname;
        }
    }

    if ($new_password1 || $new_password2) {
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

    if (!$errorCount) {
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
	$msg = gTranslate('core', "User information succesfully updated.");
        $infoLineType = 'success';
    }
    else {
        $msg = gallery_error(gTranslate('core', "User information was not succesfully updated!"));
        $infoLineType = 'error';
    }
} else if (isset($dismiss)) {
    header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
}


$tmpUser = $gallery->userDB->getUserByUsername($uname);

if (!$tmpUser) {
    echo gallery_error(gTranslate('core', "Invalid user") ." <i>$uname</i>");
    exit;
}

if ($tmpUser->isAdmin()) {
    $allowChange["create_albums"] = false;
    $allowChange["canChangeOwnPw"] = false;
} else {
    $allowChange["create_albums"] = true;
    $allowChange["canChangeOwnPw"] = true;
}

if (!strcmp($tmpUser->getUsername(), $gallery->user->getUsername())) {
    $allowChange["admin"] = true;
}

$fullname = $tmpUser->getFullname();
$email = $tmpUser->getEmail();
$defaultLanguage = $tmpUser->getDefaultLanguage();

$allowChange["uname"] = true;
$allowChange["email"] = true;
$allowChange["fullname"] = true;
$allowChange["admin"] = true;
$allowChange["default_language"] = true;
$allowChange["send_email"] = false;
$allowChange["member_file"] = false;
$allowChange["password"] = true;
$allowChange["old_password"] = false;

$canCreate = $tmpUser->canCreateAlbums() ? 1 : 0;
$isAdmin = $tmpUser->isAdmin() ? 1 : 0;
$canChangeOwnPw = $tmpUser->canChangeOwnPw() ? 1: 0;

doctype(); 
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Modify User") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo gTranslate('core', "Modify User") ?></div>
<div class="popup" align="center">
<?php 
	echo infoLine($msg, $infoLineType);
	echo gTranslate('core', "You can change any information about the user using this form.") 
?>
<br>

<?php echo makeFormIntro("modify_user.php", array('name' => 'usermodify_form')); ?>

<input type="hidden" name="old_uname" value="<?php echo $uname ?>">

<br>

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<br>
<?php echo gSubmit('save', gTranslate('core', "Save")); ?>
<?php echo gSubmit('dismiss', gTranslate('core', "Back to usermanagement")); ?>
<?php echo gButton('close', gTranslate('core', "Done"), 'parent.close()'); ?>
</form>
</div>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usermodify_form.uname.focus();
//--> 
</script>

<?php print gallery_validation_link("modify_user.php"); ?>

</body>
</html>
