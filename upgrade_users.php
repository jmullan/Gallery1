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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php

/* should only be called from init.php
*/
if (!$gallery->version) { exit; }
?>

<html>
<head>
  <title><?php echo _("Upgrading Users") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir="<?php echo $gallery->direction ?>">
<center>
<span class="title">
<?php echo _("Upgrading Users") ?>
</span>
</center><br>
<?php echo _("The user database in your gallery was created with an older version of the software and is out of date.") ?>  
<?php echo _("This is not a problem!") ?>  
<?php echo _("We will upgrade it.  This may take some time.") ?>  
<?php echo _("Your data will not be harmed in any way by this process.") ?>  
<?php echo _("Rest assured, that if this process takes a long time now, it's going to make your gallery run more efficiently in the future.") ?>  
<p>
<?php echo _("If you get an error, and only some users are upgraded, try refreshing the page to upgrade remaining users.") ?>  
<p>
<?php processingMsg(_("Please Wait...")); ?>


<?php 
if (!$gallery->userDB->integrityCheck() ) {
	print "<p>";
	echo gallery_error(_("There was a problem upgrading users.  Please check messages above, and try again"));
	$button = _("Retry");
}
else {
	print '<p>';
	print _("Users upgraded successfully.");
	$button= _("Done");
}
?>

	<center>
	<form>
	<input type="submit" value="<?php echo $button ?>" onclick='location.reload()'>
	</form>
	</center>
</body>
</html>
