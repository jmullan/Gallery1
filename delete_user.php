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
<?php
if (!$gallery->user->isAdmin()) {
	exit;	
}

if ($submit) {
	if (!strcmp($submit, "Delete")) {
		$gallery->userDB->deleteUserByUsername($uname);
		header("Location: manage_users.php");
	} else if (!strcmp($submit, "Cancel")) {
		header("Location: manage_users.php");
	}
}

?>
<html>
<head>
  <title>Delete User</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead">Delete User</span>
<br>
<br>
<?php echo makeFormIntro("delete_user.php"); ?>
<input type=hidden name=uname value=<?php echo $uname?>>

<?php
if (!strcmp($gallery->user->getUsername(), $uname)) {
	print center(gallery_error("You can't delete your own account!"));
	print "<p>";
} else {
?>
Users can have special permissions in each album.  If you delete
this user, any such permissions go away.  Users cannot be recreated.
Even if this user is recreated, those permissions are gone.  
Do you really want to delete user <b><?php echo $uname?></b>?
<p>
<p>

<input type=submit name="submit" value="Delete">
<?php
}
?>

<input type=submit name="submit" value="Cancel">
</form>

</body>
</html>
