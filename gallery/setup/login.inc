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

			);
		}
	}
}

if (isset($gallery->session->resetAdminGuid)) {
?>
<div align="center">
<div class="header"><?php printf(gTranslate('config', "Reset %s Password"), "admin"); ?></div>
<div class="desc"><?php printf(gTranslate('config', "To continue the password reset process, you must edit the file &quot;%s&quot;, remove any data currently inside it, and add the text &quot;%s&quot;.  Once you have done that, press the refresh button below.  If you know your current admin password, please remove the &quot;%s&quot; file from the server."), GALLERY_SETUPDIR . "/resetadmin", $gallery->session->resetAdminGuid, GALLERY_SETUPDIR . "/resetadmin"); ?></div>
<br>
<input type="button" onclick="location.reload()" value="<?php echo gTranslate('config', "Refresh"); ?>">
</div>
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
	echo "\n  ". '<div align="center" class="sitedesc">';
	echo gImage('icons/notice/warning.gif');

	if(isset($gallery->app->config_version) &&
	    $gallery->app->config_version != $gallery->config_version)
	{
		echo gTranslate('config', "Your Gallery needs a reconfiguration.") .
		     '<p class="shortdesc">' .
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
<tr>
	<td class="popup"><?php echo gTranslate('config', "Username") ?></td>
	<td><input type="text" name="username" value="<?php if (isset($username)) echo $username; ?>"></td>
</tr>
<tr>
	<td class="popup"><?php echo gTranslate('config', "Password") ?></td>
	<td><input type="password" name="gallerypassword"></td>
</tr>
</table>

<input type="submit" name="login" value="<?php echo gTranslate('config', "Login") ?>">
<input type="reset" name="reset" value="<?php echo gTranslate('config', "Reset") ?>">
<input type="button" name="cacle" value="<?php echo gTranslate('config', "Cancel") ?>" onClick="javascript:location.href='../'">
</form>

</div>
</div>
</body>
</html>
<?php
	exit;
} // Not Logged In
?>
