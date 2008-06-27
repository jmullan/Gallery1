<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
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

require_once(dirname(__FILE__) . '/init.php');

list($create, $bulk_create, $modify, $delete, $unames) =
    getRequestVar(array('create', 'bulk_create', 'modify', 'delete', 'unames'));

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

	$tmpUserName = $tmpUser->getUsername();
	$displayUsers[$tmpUserName] = $tmpUserName;
}
asort($displayUsers); 
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
	echo infoline(gallery_error($error),'error');
}

echo makeFormIntro('manage_users.php', array('name' => 'manageusers_form'));

echo _("You can create, modify and delete users here.");
echo "\n<p>";

if (!$displayUsers) {
	print "<i>". _("There are no users!  Create one.") ."</i>";
} else {
	echo drawSelect('unames[]', $displayUsers, '', 15, array('multiple' => ''), true);
}	

echo "\n</p>";
echo _("To select multiple users (only recognized for deletion), hold down the Control (PC) or Command (Mac) key while clicking.");
?>

<p>
<input type="submit" name="create" value="<?php echo _("Create new user") ?>">
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
