<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
 *
 * This file originally by Vallimar.
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

require_once(dirname(__FILE__) . '/init.php'); 

list($formaction, $create, $cancel) = getRequestVar(array('formaction', 'create', 'cancel'));
list($uname, $old_password, $new_password1, $new_password2) = getRequestVar(array('uname', 'old_password', 'new_password1', 'new_password2'));
list($fullname, $email, $send_email, $defaultLanguage) = getRequestVar(array('fullname', 'email', 'send_email', 'defaultLanguage'));

doctype();
?>
<html>
<head>
  <title><?php echo sprintf(_("Create User for %s."), $gallery->app->galleryTitle) ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo sprintf(_("Create User for %s."), $gallery->app->galleryTitle) ?></div>
<div class="popup" align="center">
<?php if ($gallery->app->selfReg != 'yes' || $gallery->app->emailOn == 'no') { ?>
	<p>
	<?php echo _("This Gallery does not support self-registration by visitors.") ?>
	<br><br>
	<form> <input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'> </form>
	</div>
	</div>
	</body>
	</html>
<?php
    exit();
}

$allowChange['uname'] = true;
$allowChange['password'] = false;
$allowChange['old_password'] = false;
$allowChange['fullname'] = true;
$allowChange['email'] = true;
$allowChange['default_language'] = true;
$allowChange['create_albums'] = false;
$allowChange["send_email"] = false;
$allowChange["member_file"] = false;

$errorCount=0;
if (!empty($formaction) && $formaction =='create') {
	// Security check.
	$uname = removeTags($uname);

	$gErrors['uname'] = $gallery->userDB->validNewUserName($uname);
	if ($gErrors['uname']) {
		$errorCount++;
	}

	if (empty($fullname) || !strcmp($fullname, '')) {
		$gErrors['fullname'] = _("You must specify a name.");
		$errorCount++;
	}

	if (!gallery_validate_email($email)) {
		$gErrors['email'] = _("You must specify a valid email address.");
		$errorCount++;
	}

	if (!$errorCount) {

		$password = generate_password(10);
	       	$tmpUser = new Gallery_User();
	       	$tmpUser->setUsername($uname);
	       	$tmpUser->setPassword($password);
	       	$tmpUser->setFullname($fullname);
	       	$tmpUser->setCanCreateAlbums(($gallery->app->selfRegCreate == 'yes'));
	       	$tmpUser->setEmail($email);
	       	$tmpUser->origEmail=$email;
	       	$tmpUser->log("self_register");
		$tmpUser->setDefaultLanguage($defaultLanguage);
		$msg = ereg_replace("!!PASSWORD!!", $password,
                                        ereg_replace("!!USERNAME!!", $uname,
					  ereg_replace("!!FULLNAME!!", $fullname,
					    ereg_replace("!!NEWPASSWORDLINK!!", 
						    $tmpUser->genRecoverPasswordHash(),
						    welcome_email()))));
		$logmsg = sprintf(_("%s has registered.  Email has been sent to %s."),
			$uname, $email);
		$logmsg2  = sprintf("%s has registered.  Email has been sent to %s.",
			$uname, $email);
		if ($logmsg != $logmsg2) {
			$logmsg .= " <<<<>>>>> $logmsg2";
		}

		if (gallery_mail($email, _("Gallery Self Registration"),$msg, $logmsg)) {
			$tmpUser->save();
			echo "<p>".sprintf(_("An email has been sent to %s."), $email);
			echo '<br>';
			echo _("Your account information is contained within the email.");
		} else {
			echo gallery_error(_("Email could not be sent.  Please contact gallery administrator to register on this site"));
		}
?>
		<br><br>
		<form> <input type="button" value="<?php echo _("Dismiss") ?>" onclick='parent.close()'> </form>
		</center>
		</body>
		</html>
<?php
		exit();
	}
}

echo makeFormIntro('register.php', array(
			'name' => 'usercreate_form',
			'method' => 'POST',
			'onsubmit' => "usercreate_form.create.disabled = true;"));
include(dirname(__FILE__) . '/html/userData.inc');
?>
<p>
<?php echo _("Your account information will be sent to the email address you provide.") ?>
<br><br>
<input type="hidden" name="formaction" value ="">
<input type="submit" name="create" value="<?php echo _("Create") ?>" onclick="usercreate_form.formaction.value ='create'">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.usercreate_form.uname.focus();
//--> 
</script>
</div>

<?php print gallery_validation_link("register.php"); ?>

</body>
</html>
