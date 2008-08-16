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

list($username, $gallerypassword, $login, $reset_username, $forgot) =
	getRequestVar(array('username', 'gallerypassword', 'login', 'reset_username', 'forgot'));

list($g1_return, $cmd) = getRequestVar(array('g1_return', 'cmd'));

/* decode user data, remove tags, and then re-encode using html entities for safe page display */
$username = htmlspecialchars(strip_tags(urldecode($username)));

$g1_return = urldecode($g1_return);

if(!isValidGalleryUrl($g1_return) || empty($g1_return)) {
	$g1_return = makeGalleryHeaderUrl();
}

$loginFailure = array();
$resetInfo = array();

if(!empty($cmd) && $cmd === 'logout') {
	gallery_syslog("Logout by ". $gallery->session->username ." from ". $_SERVER['REMOTE_ADDR']);
	$gallery->session->username = '';
	$gallery->session->language = '';
	destroyGallerySession();

	// Prevent the 'you have to be logged in' error message
	// when the user logs out of a protected album
	createGallerySession();
	$gallery->session->gRedirDone = true;

	header("Location: $g1_return");
}

if (!empty($username) && !empty($gallerypassword) && !empty($login)) {
	$tmpUser = $gallery->userDB->getUserByUsername($username);
	if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword)) {

		// User is successfully logged in, regenerate a new
		// session ID to prevent session fixation attacks
		createGallerySession(true);

		// Perform the login
		$tmpUser->log("login");
		$tmpUser->save();
		$gallery->session->username = $username;
		gallery_syslog("Successful login for $username from " . $_SERVER['REMOTE_ADDR']);

		if ($tmpUser->getDefaultLanguage() != "") {
			$gallery->session->language = $tmpUser->getDefaultLanguage();
		}

		if (!$gallery->session->offline) {
			//echo $g1_return;
			header("Location: $g1_return");
		}
		else {
			echo '<span class="g-attention">'. gTranslate('core', "SUCCEEDED") . '</span><p>';
			return;
		}
	}
	else {
		$loginFailure[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Invalid username or password.")
		);
		$gallerypassword = null;
		gallery_syslog("Failed login for $username from " . $_SERVER['REMOTE_ADDR']);
	}
}
elseif (!empty($login) && empty($forgot)) {
	$loginFailure[] = array(
		'type' => 'information',
		'text' => gTranslate('core', "Please enter username and password!")
	);
}
elseif (!empty($forgot) && empty($reset_username)) {
	$resetInfo[] = array(
		'type' => 'information',
		'text' => gTranslate('core', "Please enter <i>your</i> username.")
	);
}
elseif (!empty($forgot) && !empty($reset_username)) {
	$tmpUser = $gallery->userDB->getUserByUsername($reset_username);

	if ($tmpUser) {
		$wait_time = 15;
		if ($tmpUser->lastAction ==  "new_password_request" &&
			(time() - $tmpUser->lastActionDate) < ($wait_time * 60)) {
			$resetInfo[] = array(
				'type' => 'error',
				'text' => sprintf(gTranslate('core', "The last request for a password was less than %d minutes ago.  Please check for previous email, or wait before trying again."), $wait_time)
			);
		}
		else if (check_email($tmpUser->getEmail())) {
			if (gallery_mail($tmpUser->email,
				  gTranslate('core', "New password request"),
				  sprintf(gTranslate('core', "Someone requested a new password for user %s from Gallery '%s' on %s. You can create a password by visiting the link below. If you didn't request a password, please ignore this mail. "), $reset_username, $gallery->app->galleryTitle, $gallery->app->photoAlbumURL) . "\n\n" .
				  sprintf(gTranslate('core', "Click to reset your password: %s"),
				  $tmpUser->genRecoverPasswordHash()) . "\n",
				  sprintf(gTranslate('core', "New password request %s"), $reset_username)))
			{
				$tmpUser->log("new_password_request");
				$tmpUser->save();
				$resetInfo[] = array(
					'type' => 'success',
					'text' => sprintf(gTranslate('core', "An email has been sent to the address stored for %s.  Follow the instructions to change your password.  If you do not receive this email, please contact the Gallery administrators."), $reset_username)
				);
			}
			else {
				$resetInfo[] = array(
					'type' => 'error',
					'text' => gTranslate('core', "Email could not be sent.") .
							  "<br>"  .
							  sprintf(gTranslate('core', "Please contact %s administrators for a new password."), $gallery->app->galleryTitle)
				);
			}
		}
		else {
			$resetInfo[] = array(
					'type' => 'warning',
					'text' => gTranslate('core', "There is no valid email for this account.") .
							  "<br>" .
							  sprintf(gTranslate('core', "Please contact %s administrators for a new password."), $gallery->app->galleryTitle)
			);
		}
	}
	else {
		$resetInfo[] = array(
			'type' => 'error',
			'text' => gTranslate('core', "Invalid username.")
		);
	}
}

$title = sprintf(gTranslate('core', "Login to %s"), $gallery->app->galleryTitle);

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo clearGalleryTitle(gTranslate('core', "Login")); ?></title>
  <?php
	common_header();
?>
</head>
<body>
<?php
}
includeTemplate("gallery.header", '', 'classic');

$adminbox['text']	= gTranslate('common', "Login");
$adminbox['commands']	= languageSelector();

includeLayout('adminbox.inc');

?>

<div class="g-sitedesc">
	<?php echo gTranslate('core', "Logging in gives you greater permission to view, create, modify and delete albums."); ?>
</div>
<div class="g-album-vertical-spacer"></div>

<?php echo infoBox($loginFailure); ?>
<div class="g-loginpage">
<fieldset>
<legend class="g-emphasis"><?php echo gTranslate('common', "Login") ?></legend>
<?php
echo makeFormIntro('login.php', array('name' => 'loginForm'));
?>
 	<table>
<?php
	echo gInput('text', 'username', gTranslate('core', "_Username"), true, $username,array('class' => 'g-form-text g-usernameInput'));

	echo gInput('password', 'gallerypassword', gTranslate('core', "_Password"), true, null, array('class' => 'g-form-text g-passwordInput'));
?>
	</table>

 	<p align="center">
	<?php echo gSubmit('login', gTranslate('core', "_Login")); ?>
	<?php echo gButton('cancel', gTranslate('core', "_Cancel"), "location.href='$g1_return'"); ?>
	</p>

	<?php echo gInput('hidden', 'g1_return', '', false, urlencode($g1_return)); ?>
	</form>
</fieldset>

<?php
if (isset($gallery->app->emailOn) && $gallery->app->emailOn == 'yes') {
?>

<fieldset>
    <legend class="g-sectioncaption g-emphasis"><?php echo gTranslate('core', "Forgotten your password?") ?></legend>
<?php
  echo makeFormIntro('login.php', array('name' => 'resetForm'));
	echo infoBox($resetInfo);

	echo gInput('text', 'reset_username', gTranslate('core', "Username"), false, $username, array('class' => 'g-form-text g-usernameInput'));
	echo "\n<p align=\"center\">";
	echo gSubmit('forgot', gTranslate('core', "_Send me my password"));
	echo "\n</p>";
?>

  </form>
</fieldset>

<?php } /* End if-email-on */

if ($gallery->app->selfReg == 'yes') {
?>
<fieldset>
    <legend class="g-sectioncaption g-emphasis"><?php echo gTranslate('core', "No account at all?"); ?></legend>
    <div class="center">
	<?php echo gButton('register', gTranslate('core', "_Register a new account."), popup('register.php')); ?>
	</div>
</fieldset>
<?php
}
?>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.loginForm.username.focus();
//-->
</script>

</div>
<?php require_once(GALLERY_BASE .'/templates/info_donation-block.tpl.default'); ?>
<?php includeTemplate("overall.footer"); ?>
</body>
</html>

