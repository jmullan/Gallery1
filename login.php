<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>

<?php
// Security check.
if (!isset($uname)) {
	$uname="";
}
$uname = removeTags($uname);
?>

<html>
<head>
	<title><?php echo sprintf(_("Login to %s"), $gallery->app->galleryTitle) ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead"><?php echo sprintf(_("Login to %s"), $gallery->app->galleryTitle) ?></span>
<br>
<br>
<?php
if (isset($login)) {
	if ($uname && $gallerypassword) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword)) {
			$gallery->session->username = $uname;
			if ($tmpUser->getDefaultLanguage() != "") {
				$gallery->session->language = 
					$tmpUser->getDefaultLanguage();
			}
			if (!$gallery->session->offline) {
				dismissAndReload();
			} else {
				print "<span class=error>SUCCEEDED</span><p>";
				return;
			}
		} else {
			$invalid = 1;
			$gallerypassword = null;
		}
	} else {
		$error = 1;
	}
}
?>

<?php echo makeFormIntro("login.php", array("name" => "login_form", "method" => "POST")); ?>
<?php echo _("Logging in gives you greater permission to view, create, modify and delete albums.") ?>
<p>
<table>
<?php if (isset($invalid)) { ?>
 <tr>
  <td colspan=2>
   <?php echo gallery_error(_("Invalid username or password")); ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <?php echo _("Username") ?>
  </td>
  <td>
   <input type=text name="uname" value=<?php echo $uname ?>>
  </td>
 </tr>

<?php if (isset($error) && !isset($uname)) { ?>
 <tr>
  <td colspan=2 align=center>
   <?php echo gallery_error(_("You must specify a username")); ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
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
<input type="submit" name="login" value="<?php _("Login") ?>">
<input type="button" name="cancel" value="<?php echo _("Cancel") ?>" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.login_form.uname.focus();
//--> 
</script>

</body>
</html>
