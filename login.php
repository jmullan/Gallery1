<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 */
?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>

<html>
<head>
  <title>Login to <?php echo $gallery->app->galleryTitle?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead">Login to <?php echo $gallery->app->galleryTitle?></span>
<br>
<br>
<?php
if ($submit) {
	if ($uname && $gallerypassword) {
		$tmpUser = $gallery->userDB->getUserByUsername($uname);
		if ($tmpUser && $tmpUser->isCorrectPassword($gallerypassword)) {
			$gallery->session->username = $uname;
			dismissAndReload();
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
Logging in gives you greater permission to
<br>
view, create, modify and delete albums.
<p>
<table>
<?php if ($invalid) { ?>
 <tr>
  <td colspan=2>
   <?php echo error("Invalid username or password"); ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   Username
  </td>
  <td>
   <input type=text name="uname" value=<?php echo $uname?>>
  </td>
 </tr>

<?php if ($error && !$uname) { ?>
 <tr>
  <td colspan=2 align=center>
   <?php echo error("You must specify a username"); ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   Password
  </td>
  <td>
   <input type=password name="gallerypassword" value=<?php echo $gallerypassword?>>
  </td>
 </tr>

<?php if ($error && !$gallerypassword) { ?>
 <tr>
  <td colspan=2 align=center>
   <?php echo error("You must specify a password"); ?>
  </td>
 </tr>
<?php } ?>

</table>
<p>
<input type=submit name="submit" value="Login">
<input type=submit name="submit" value="Cancel" onclick='parent.close()'>
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
document.login_form.uname.focus();
//--> 
</script>

</body>
</html>
