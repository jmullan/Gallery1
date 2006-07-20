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

list($login, $username, $gallerypassword) =
	getRequestVar(array('login', 'username', 'gallerypassword'));

if (isset($gallery->session->username) && !empty($gallery->session->username)) {
	$tmpUser = $gallery->userDB->getUserByUsername($gallery->session->username);
}

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
			if ($tmpUser->getDefaultLanguage() != "") {
				$gallery->session->language =
					$tmpUser->getDefaultLanguage();
			}
		}
		elseif (!isset($tmpUser) || !$tmpUser->isCorrectPassword($gallerypassword)) {
			$tmpUser = null;
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);

			$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('config', "Invalid username or password")
			);
		}
		elseif (!$tmpUser->isAdmin()) {
			$gallerypassword = null;
			gallery_syslog("Failed config wizard login for $username from " . $_SERVER['REMOTE_ADDR']);

			$messages[] = array(
				'type' => 'error',
				'text' => gTranslate('config', "User is not an admin - access denied")
			);
		}
	}
}

if (isset($gallery->session->resetAdminGuid)) {
?>
<div class="g-header">
    <div class="g-pagetitle"><?php echo sprintf(gTranslate('config', "Reset %s Password"), "admin"); ?></div>
</div>
<div class="g-sitedesc"><?php
    printf(gTranslate('config', "To continue the password reset process, you must edit the file &quot;%s&quot;, remove any data currently inside it, and add the text &quot;%s&quot;.  Once you have done that, press the refresh button below.  If you know your current admin password, please remove the &quot;%s&quot; file from the server."), GALLERY_SETUPDIR . "/resetadmin", $gallery->session->resetAdminGuid, GALLERY_SETUPDIR . "/resetadmin");
?></div>
<p align="center">
<input type="button" onclick="location.reload()" value="<?php echo gTranslate('config', "Refresh"); ?>">
</p>
</body>
</html>
<?php
	exit;
}
elseif (!isset($tmpUser) || !$tmpUser->isAdmin()) {  // Not logged in
?>

<div class="g-header">
    <div class="g-pagetitle"><?php echo sprintf (gTranslate('config', "%s configuration"), gallery()) ?></div>
</div>

<div class="g-content-popup">

<div class="g-sitedesc left"><?php
    echo gTranslate('config', "You must log in as an administrator to edit this Gallery configuration.");
    echo "\n<br>";
    echo gTranslate('config', "If you have forgotten the password to your admin account, you can easily reset it.");
    echo "\n<br>";
    printf (gTranslate('config', "Create a blank file named '%s' in your %s directory, then refresh this page and follow the instructions on this page."), 'resetadmin', GALLERY_SETUPDIR);
?></div>

<?php
	echo infoBox($messages);
	echo makeFormIntro("setup/" . $target, array('name' => 'login_form'));
?>
<table align="center">
<?php

echo gInput('text', 'username', gTranslate('core', "_Username"), true, false, $username);

echo gInput('password', 'gallerypassword', gTranslate('core', "_Password"), true);
?>
</table>

<p class="center">
<?php
	echo gSubmit('login', gTranslate('config', "_Log me in"));
	echo gReset('reset', gTranslate('config', "_Reset"));
	echo gButton('cancel', gTranslate('config', "_Cancel"), "javascript:location.href='../'");
?>
</p>

</form>

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
