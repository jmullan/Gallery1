<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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

list($username, $gallerypassword, $login, $reset_username, $forgot) =
    getRequestVar(array('username', 'gallerypassword', 'login', 'reset_username', 'forgot'));

/* decode user data, remove tags, and then re-encode using html entities for safe page display */
$username = htmlspecialchars(strip_tags(urldecode($username)));

$loginFailure = array();
$resetInfo = array();

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
			dismissAndReload();
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
elseif (!empty($forgot)) {
    $tmpUser = $gallery->userDB->getUserByUsername($reset_username);

    if ($tmpUser) {
        $wait_time = 0;
        if ($tmpUser->lastAction ==  "new_password_request" &&
            (time() - $tmpUser->lastActionDate) < ($wait_time * 60)) {
            $resetInfo[] = array(
                'type' => 'error',
                'text' => sprintf(gTranslate('core', "The last request for a password was less than %d minutes ago.  Please check for previous email, or wait before trying again."), $wait_time)
            );
        }
        else if (!check_email($tmpUser->getEmail())) {
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
                              sprintf(gTranstlate('core', "Please contact %s administrators for a new password."), $gallery->app->galleryTitle)
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

$header = $title = sprintf(gTranslate('core', "Login to %s"), $gallery->app->galleryTitle);


/* HTML Output Start */
    doctype();
?>
<html>
<head>
  <title><?php echo $title; ?></title>
  <?php common_header(); ?>
</head>
<body class="g-popup">
    <div class="g-header-popup">
      <div class="g-pagetitle-popup"><?php echo $header ?></div>
    </div>

<?php echo makeFormIntro('login.php', array(), array('type' => 'popup')); ?>

<div class="g-content-popup">

<?php echo gTranslate('core', "Logging in gives you greater permission to view, create, modify and delete albums."); ?>

<?php echo infoBox($loginFailure); ?>

<table align="center">
<?php
echo gInput('text', 'username', gTranslate('core', "_Username"), true, $username);

echo gInput('password', 'gallerypassword', gTranslate('core', "_Password"), true);
?>
</table>


<p align="center">
	<?php echo gSubmit('login', gTranslate('core', "_Login")); ?>
	<?php echo gButton('cancel', gTranslate('core', "_Cancel"), 'parent.close()'); ?>
</p>
</div>


<?php
if (isset($gallery->app->emailOn) && $gallery->app->emailOn == 'yes') {
?>
<div class="g-sectioncaption-popup"><?php echo gTranslate('core', "Forgotten your password?") ?></div>
<div class="g-content-popup" align="center">
<?php
    echo infoBox($resetInfo);

    echo gInput('text', 'reset_username', gTranslate('core', "Username"), false, $username);
    echo "\n<p>";
    echo gSubmit('forgot', gTranslate('core', "_Send me my password"));
    echo "\n</p>";
?>
</div>

</form>

<?php } /* End if-email-on */
if ($gallery->app->selfReg == 'yes') {
?>
<div class="g-sectioncaption-popup"><?php echo gTranslate('core', "No account at all?") ?></div>
<div class="g-content-popup" align="center">
<?php echo galleryLink(makeGalleryUrl('register.php', array('type' => 'popup')), gTranslate('core', "_Register a new account.")); ?>
</div>
<?php
}
?>

<script language="javascript1.2" type="text/JavaScript">
<!--
// position cursor in top form field
document.g1_form.username.focus();
//-->
</script>

</body>
</html>

