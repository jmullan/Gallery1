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
if (!$gallery->user->isAdmin()) {
	exit;	
}

if (isset($delete)) {
		$gallery->userDB->deleteUserByUsername($uname);
		header("Location: manage_users.php");
}
if (isset($cancel)) {
	header("Location: manage_users.php");
}

?>
<html>
<head>
  <title><?php echo _("Delete User") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<center>
<span class="popuphead"><?php echo _("Delete User") ?></span>
<br>
<br>
<?php echo makeFormIntro("delete_user.php"); ?>
<input type="hidden" name="uname" value="<?php echo $uname ?>">

<?php
if (!strcmp($gallery->user->getUsername(), $uname)) {
	print center(gallery_error(_("You can't delete your own account!")));
	print "<p>";
} else {
?>
<?php echo _("Users can have special permissions in each album.") ?>
<?php echo _("If you delete this user, any such permissions go away.") ?>
<?php echo _("Users cannot be recreated.") ?>
<?php echo _("Even if this user is recreated, those permissions are gone.") ?>
<?php echo _("Do you really want to delete user") ?> <b><?php echo $uname ?></b>?
<p>
<p>

<input type="submit" name="delete" value="<?php echo _("Delete") ?>">
<?php
}
?>

<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>">
</form> 

</body>
</html>
