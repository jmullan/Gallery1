<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
if (isset($username) && isset($gallerypassword)) {
	$tmpUser = $gallery->userDB->getUserByUsername($username);
	if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword) && $tmpUser->isAdmin()) {

		$tmpUser->log("login");
		$tmpUser->save();
		$gallery->session->username = $username;
		gallery_syslog("Successful config wizard login for $username from " . $HTTP_SERVER_VARS['REMOTE_ADDR']);
		if ($tmpUser->getDefaultLanguage() != "") {
			$gallery->session->language = 
				$tmpUser->getDefaultLanguage();
		}
		echo "<input type='hidden' name='username' value='$username'>";
		echo "<input type='hidden' name='gallerypassword' value='$gallerypassword'>";
	}
	elseif (!isset($tmpUser) || !$tmpUser->isCorrectPassword($gallerypassword)) {
		$invalid = 1;
		$gallerypassword = null;
		gallery_syslog("Failed config wizard login for $username from " . $HTTP_SERVER_VARS['REMOTE_ADDR']);
	}
	elseif (!$tmpUser->isAdmin()) {
		$notadmin = 1;
		$gallerypassword = null;
		gallery_syslog("Failed config wizard login for $username from " . $HTTP_SERVER_VARS['REMOTE_ADDR']);
	}
}

// Not logged in
if (!isset($tmpUser) || !$tmpUser->isAdmin()) { 
?>
</form>
<div class="popup">
<?php echo makeFormIntro("setup/index.php", array("name" => "login_form", "method" => "POST")); ?>
<?php echo _("You must log in as an administrator to edit this server's configuration.") ?>
<p>
<table>
<?php if (isset($invalid)) { ?>
 <tr>
  <td colspan=2>
   <?php echo gallery_error(_("Invalid username or password")); ?>
  </td>
 </tr>
<?php } elseif (isset($notadmin)) {?>
 <tr>
  <td colspan=2>
   <?php echo gallery_error(_("User is not an admin - access denied")); ?>
  </td>
 </tr>
<?php } ?>
 <tr>
  <td class="popup">
   <?php echo _("Username") ?>
  </td>
  <td>
   <input type=text name="username" value="<?php if (isset($username)) echo $username; ?>">
  </td>
 </tr>

<?php if (isset($error) && !isset($username)) { ?>
 <tr>
  <td colspan=2 align=center>
   <?php echo gallery_error(_("You must specify a username")); ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td class="popup">
	<?php echo _("Password") ?>
  </td>
  <td>
   <input type=password name="gallerypassword">
  </td>
 </tr>

<?php if (isset($error) && !isset($gallerypassword)) { ?>
 <tr>
  <td colspan=2 align=center>
   <?php echo gallery_error(_("You must specify a password")); ?>
  </td>
 </tr>
<?php } ?>

</table>
<p>
<input type="submit" name="login" value="<?php echo _("Login") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>
</body>
</html>
<?php
	exit;
} // Not Logged In ?>