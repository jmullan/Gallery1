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

require_once(dirname(__FILE__) . '/init.php');

list($create, $bulkcreate, $modify, $delete, $unames) =
                getRequestVar(array('create', 'bulkcreate', 'modify', 'delete', 'unames'));

if (!$gallery->user->isAdmin()) {
	echo _("You are not allowed to perform this action!");
	exit;	
}

if (!empty($create)) {
	header("Location: " . makeGalleryHeaderUrl("create_user.php"));
}
if (!empty($bulk_create)) {
	header("Location: " . makeGalleryHeaderUrl("multi_create_user.php"));
}

if ( (isset($modify) || isset($delete)) && ! isset($unames)) {
	$error=_("Please select a user");
} elseif (isset($modify)) {
	header("Location: " . makeGalleryHeaderUrl("modify_user.php", array('uname' => $unames[0])));
} elseif (isset($delete)) {
	header("Location: " . makeGalleryHeaderUrl("delete_user.php", array('unames' => $unames)));
}

$displayUsers = array();
foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	if ($tmpUser->isPseudo()) {
		continue;
	}

	array_push($displayUsers, $tmpUser->getUsername());
}
sort($displayUsers); 
doctype();
?>
<html>
<head>
  <title><?php echo _("Manage Users") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Manage Users") ?></div>
<div class="popup" align="center">
<?php 
if (isset($error)) {
	echo gallery_error($error);
}

echo makeFormIntro("manage_users.php", array(
			"name" => "manageusers_form", 
			"method" => "POST"));

echo _("You can create, modify and delete users here.");
echo "\n<p>";

if (!$displayUsers) {
	print "<i>". _("There are no users!  Create one.") ."</i>";
} else {
	echo '<select name="unames[]" size="15" multiple>';
	foreach ($displayUsers as $name) {
		print "\t<option value=\"$name\">$name</option>\n";
	}
	echo "\n</select>";
}	

echo "\n</p>";
echo _("To select multiple users (only recognized for deletion), hold down the Control (PC) or Command (Mac) key while clicking.");
?>

<p>
<input type="submit" name="create" value="<?php echo _("Create") ?>"> 
<?php if ($gallery->app->multiple_create == "yes") { ?>
	<input type="submit" name="bulk_create" value="<?php echo _("Bulk Create") ?>"> 
<?php }
if (count($displayUsers)) { ?>
<input type="submit" name="modify" value="<?php echo _("Modify") ?>">
<input type="submit" name="delete" value="<?php echo _("Delete") ?>">
<?php } ?>
<input type="button" value="<?php echo _("Done") ?>" onclick='parent.close()'>
</form>

</div>
<?php print gallery_validation_link("manage_users.php"); ?>

</body>
</html>
