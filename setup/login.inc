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

list($login, $username, $gallerypassword) =
	getRequestVar(array('login', 'username', 'gallerypassword'));

if (isset($gallery->session->username) && !empty($gallery->session->username)) {
	$tmpUser = $gallery->userDB->getUserByUsername($gallery->session->username);
}

/* Cleanup usergiven values */
$username = sanitizeInput($username);

$messages = array();

if (fs_file_exists(GALLERY_SETUPDIR . "/resetadmin")) {
	if (!isset($gallery->session->resetAdminGuid)) {
		$gallery->session->resetAdminGuid = md5(uniqid(mt_rand(), true));
	}
}
elseif (!empty($login)) {
	if (empty($username) || empty($gallerypassword)) {
		$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('config', "Please enter username and password.")
		);
	}
	else {
		$tmpUser = $gallery->userDB->getUserByUsername($username);
		if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword) && $tmpUser->isAdmin()) {
			$tmpUser->log("login");
			$tmpUser->save();
			$gallery->session->username = $username;

			gallery_syslog("Successful config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);
			if ($tmpUser->getDefaultLanguage() != '') {
				$gallery->session->language = $tmpUser->getDefaultLanguage();
			}
		}
		elseif ($tmpUser) {
			$tmpUser = null;
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);

			$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('config', "Invalid username or password, or user is not an admin.")
			);
		}
		else {
			$tmpUser = null;
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login from " . $_SERVER['REMOTE_ADDR']);

			$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('config', "User is not an admin - access denied.")
			);
		}
	}
}

if (isset($gallery->session->resetAdminGuid)) {
	doctype();
?>
<html>
<head>
  <title><?php printf(gTranslate('config', "Reset %s Password"), "admin") ?></title>
  <?php common_header(); ?>
</head>
<body>
	<div class="g-header">
		<div class="g-pagetitle"><?php printf(gTranslate('config', "Reset %s Password"), "admin") ?></div>
	</div>
<div class="g-sitedesc"><?php
    printf(gTranslate('config', "To continue the password reset process, you must edit the file &quot;%s&quot;, remove any data currently inside it, and add the text &quot;%s&quot;."), GALLERY_SETUPDIR . '/resetadmin', '<span class="g-information">'. $gallery->session->resetAdminGuid . '</span>');
    echo "\n<br>";
    printf(gTranslate('config', "Once you have done that, press the refresh button below.  If you know your current admin password, please remove the &quot;%s&quot; file from the server."), GALLERY_SETUPDIR . "/resetadmin");
    echo "\n<br>";
?></div>
<p align="center">
<?php echo gButton('refresh',  gTranslate('config', "Refresh"), 'location.reload()'); ?>
</p>
</body>
</html>
<?php
	exit;
}
elseif (!isset($tmpUser) || !$tmpUser->isAdmin()) {
	if (!headers_sent() && (ob_get_length() == 0 || ! ob_get_length())) {
		printPopupStart(gTranslate('config', "Gallery"));
	}
	// Not logged in
	echo "\n  ". '<div class="g-warning left g-message">';
	echo gImage('icons/notice/warning.gif');

	if(isset($gallery->app->config_version) &&
	    $gallery->app->config_version != $gallery->config_version)
	{
		echo gTranslate('config', "Your Gallery needs a reconfiguration.") .
		     '<p>' .
		     gTranslate('config', "Your Gallery settings were configured with an older version of Gallery, and are out of date. Please re-run the Configuration Wizard!") .
		     '</p>';
	}

        echo gTranslate('config', "You must log in as an administrator to edit this Gallery configuration.");
        echo "\n<br><br>";
        echo gTranslate('config', "If you have forgotten the password to your admin account, you can easily reset it.");
        echo "\n<br>";
        printf (gTranslate('config', "Create a blank file named '%s' (yes, no extension) in the <i><b>setup</b></i> directory of your Gallery, then refresh this page and follow the instructions on this page."), 'resetadmin');

	echo "\n</div>";

	echo "\n<div align=\"center\">";
    	echo infoBox($messages);
    	echo makeFormIntro("setup/" . $target, array('name' => 'login_form'));
    ?>

    <table align="center">
    <?php

    echo gInput('text', 'username', gTranslate('core', "_Username"), true, $username);
    echo gInput('password', 'gallerypassword', gTranslate('core', "_Password"), true);
    ?>
    </table>
    <br>
    <?php
    	echo gSubmit('login', gTranslate('config', "_Log me in"));
    	echo gReset('reset', gTranslate('config', "_Reset"));
    	echo gButton('cancel', gTranslate('config', "_Cancel"), "javascript:location.href='../'");
    ?>
    </form>

    </div>
</div>
<script type="text/javascript">
	document.login_form.username.focus();
</script>
</body>
</html>
<?php
	exit;
} // Not Logged In
?>
