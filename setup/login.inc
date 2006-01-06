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

list($login, $username, $gallerypassword) = getRequestVar(array('login', 'username', 'gallerypassword'));

if (isset($gallery->session->username) && !empty($gallery->session->username)) {
	$tmpUser = $gallery->userDB->getUserByUsername($gallery->session->username);
}

if (fs_file_exists(GALLERY_SETUPDIR . "/resetadmin")) {
	if (!isset($gallery->session->resetAdminGuid)) {
		$gallery->session->resetAdminGuid = md5(uniqid(mt_rand(), true));
	}
}
elseif (!empty($login)) {
	if (empty($username) || empty($gallerypassword)) {
		$error = _("Please enter username and password.");
	} else {
		$tmpUser = $gallery->userDB->getUserByUsername($username);
		if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword) && $tmpUser->isAdmin()) {
			$tmpUser->log("login");
			$tmpUser->save();
			$gallery->session->username = $username;
			gallery_syslog("Successful config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);
			if ($tmpUser->getDefaultLanguage() != "") {
				$gallery->session->language = 
					$tmpUser->getDefaultLanguage();
			}
		}
		elseif (!isset($tmpUser) || !$tmpUser->isCorrectPassword($gallerypassword)) {
			$tmpUser = null;
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);
			$error= _("Invalid username or password");
		}
		elseif (!$tmpUser->isAdmin()) {
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);
			$error=_("User is not an admin - access denied");
		}
	}
}

if (isset($gallery->session->resetAdminGuid)) {
?>
<div align="center">
<div class="header"><?php echo sprintf(_("Reset %s Password"), "admin"); ?></div>
<div class="desc"><?php echo sprintf(_("To continue the password reset process, you must edit the file &quot;%s&quot;, remove any data currently inside it, and add the text &quot;%s&quot;.  Once you have done that, press the refresh button below.  If you know your current admin password, please remove the &quot;%s&quot; file from the server."), GALLERY_SETUPDIR . "/resetadmin", $gallery->session->resetAdminGuid, GALLERY_SETUPDIR . "/resetadmin"); ?></div>
<br>
<input type="button" onclick="location.reload()" value="<?php echo _("Refresh"); ?>">
</body>
</html>
<?php
	exit;
}
elseif (!isset($tmpUser) || !$tmpUser->isAdmin()) {  // Not logged in 
	echo makeFormIntro("setup/" . $target, array('name' => 'login_form'));
?>

<div align="center">
<div class="header"><?php echo sprintf (_("%s configuration"), gallery()) ?></div>
<div class="desc"><?php echo _("You must log in as an administrator to edit this server's configuration.") ?></div>
<div class="desc"><?php echo sprintf(_("If you have forgotten the password to your admin account, you can reset it by creating a blank file named '%s' in your %s directory and then refreshing and following the instructions on this page."), 'resetadmin', GALLERY_SETUPDIR) ?></div>
<p>
<?php if(isset($error)) {
	echo gallery_error($error) ."</p>\n<p>";
}
?>

<table>
<tr>
	<td class="popup"><?php echo _("Username") ?></td>
	<td><input type="text" name="username" value="<?php if (isset($username)) echo $username; ?>"></td>
</tr>
<tr>
	<td class="popup"><?php echo _("Password") ?></td>
	<td><input type="password" name="gallerypassword"></td>
</tr>
</table>
</p>

<input type="submit" name="login" value="<?php echo _("Login") ?>">
<input type="reset" name="reset" value="<?php echo _("Reset") ?>">
<input type="button" name="cacle" value="<?php echo _("Cancel") ?>" onClick="javascript:location.href='../'">
</form>

</body>
</html>
<?php
	exit;
} // Not Logged In
?>
