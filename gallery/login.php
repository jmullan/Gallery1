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

require_once(dirname(__FILE__) . '/init.php');

list($username, $gallerypassword, $forgot, $login) = getRequestVar(array('username', 'gallerypassword', 'forgot', 'login'));

/* decode user data, remove tags, and then re-encode using html entities for safe page display */
$username = htmlspecialchars(strip_tags(urldecode($username)));

if (!empty($username) && !empty($gallerypassword)) {
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

		$defaultLanguage = $tmpUser->getDefaultLanguage();
		if (!empty($defaultLanguage)) {
			$gallery->session->language = $defaultLanguage;
		}

		if (!$gallery->session->offline) {
			dismissAndReload();
		}
		else {
		       	echo '<span class="error">'. gTranslate('core', "SUCCEEDED") . '</span><p>';
			return;
		}
	}
	else {
		$error = gTranslate('core', "Invalid username or password");
		$gallerypassword = null;
		gallery_syslog("Failed login for $username from " . $_SERVER['REMOTE_ADDR']);
	}
}
elseif (!empty($login)) {
	$error = gTranslate('core', "Please enter username and password.");
}

doctype();
?>
<html>
<head>
  <title><?php echo sprintf(gTranslate('core', "Login to %s"), $gallery->app->galleryTitle) ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
	<div class="popuphead"><?php echo sprintf(gTranslate('core', "Login to %s"), $gallery->app->galleryTitle) ?></div>
	<div class="popup" align="center">
	
	<?php echo makeFormIntro('login.php', array('name' => 'login_form')); ?>
	<?php echo gTranslate('core', "Logging in gives you greater permission to view, create, modify and delete albums.") ?>
	
	<table align="center">
	<?php if (isset($error)) { ?>
	<tr>
		<td colspan="2" align="left"><?php echo gallery_error($error); ?></td>
	</tr>
	<?php } ?>
	
	<tr>
		<td><?php echo gTranslate('core', "Username") ?></td>
		<td><input type="text" name="username"  class="popupform" value="<?php echo $username ?>"></td>
	</tr>
	<tr>
		<td><?php echo gTranslate('core', "Password") ?></td>
		<td><input type="password" name="gallerypassword" class="popupform"></td>
	</tr>
	</table>
	
	<p align="center">
		<input type="submit" name="login" value="<?php echo gTranslate('core', "Login") ?>">
		<input type="button" name="cancel" value="<?php echo gTranslate('core', "Cancel") ?>" onclick='parent.close()'>
	</p>
	</form>
	</div>
	<?php
	if (isset($gallery->app->emailOn) && $gallery->app->emailOn == 'yes') {
	?>
	<div class="popuphead"><?php echo gTranslate('core', "Forgotten your password?") ?></div>
	<div class="popup" align="center">
	<?php
	    echo makeFormIntro('login.php', array('name' => 'forgot_form'));
	
	    if (!empty($forgot)) {
	    	$tmpUser = $gallery->userDB->getUserByUsername($username);
	    	if ($tmpUser) {
	    		$wait_time=15;
	    		if ($tmpUser->lastAction ==  "new_password_request" &&
	    			time() - $tmpUser->lastActionDate < $wait_time * 60)
	    		{
	    			echo gallery_error(sprintf(gTranslate('core', "The last request for a password was less than %d minutes ago.  Please check for previous email, or wait before trying again."), $wait_time));
	
	    		}
	    		else if (check_email($tmpUser->getEmail())) {
	    			if (gallery_mail(
						$tmpUser->email,
						gTranslate('core', "New password request"),
						sprintf(gTranslate('core', "Someone requested a new password for user %s from Gallery '%s' on %s. You can create a password by visiting the link below. If you didn't request a password, please ignore this mail. "), $username, $gallery->app->galleryTitle, $gallery->app->photoAlbumURL) . "\n\n" .
						sprintf(gTranslate('core', "Click to reset your password: %s"),
						$tmpUser->genRecoverPasswordHash()) . "\n",
						sprintf(gTranslate('core', "New password request %s"), $username)))
					{
						$tmpUser->log("new_password_request");
	    				$tmpUser->save();
				       	echo sprintf(gTranslate('core', "An email has been sent to the address stored for %s.  Follow the instructions to change your password.  If you do not receive this email, please contact the Gallery administrators."),$username)  ?>
						<br><br>
				       	<form> <input type="button" value="<?php echo gTranslate('core', "Dismiss") ?>" onclick='parent.close()'> </form>
					<?php
	    			}
	    			else {
	    				echo gallery_error(sprintf(gTranslate('core', "Email could not be sent.  Please contact %s administrators for a new password"),$gallery->app->galleryTitle ));
	    			}
	    			return;
	    		}
	    		else {
	    			echo gallery_error(sprintf(gTranslate('core', "There is no valid email for this account.  Please contact %s administrators for a new password"),$gallery->app->galleryTitle ));
	    		}
	    	}
	    	else {
	    		echo gallery_error(gTranslate('core', "Not a valid username"));
	    	}
	    }
	?>
	
	<table align="center">
	<tr>
		<td><?php echo gTranslate('core', "Username") ?></td>
		<td><input type="text" name="username"  class="popupform" value="<?php echo $username ?>"></td>
	</tr>
	</table>
	
	<p align="center"><input type="submit" name="forgot" value="<?php echo gTranslate('core', "Send me my password") ?>"></p>
	</form>
	</div>
	
	<?php } /* End if-email-on */
	if ($gallery->app->selfReg == 'yes') {
	?>
	<div class="popuphead"><?php echo gTranslate('core', "No account at all?") ?></div>
	<div class="popup" align="center">
	<a href="<?php echo makeGalleryUrl('register.php') ?>"><?php echo gTranslate('core', "Register a new account."); ?></a>
	</div>
	<?php
	}
	?>
	
	<script language="javascript1.2" type="text/JavaScript">
	<!--
	// position cursor in top form field
	document.login_form.username.focus();
	//-->
	</script>
	

<?php print gallery_validation_link("login.php"); ?>

</body>
</html>

