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
	print "Security violation\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = '';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
if (!$gallery->user->isAdmin()) {
	exit;	
}

if ($action) {
	if (!strcmp($action, "Create")) {
		header("Location: create_user.php?uname=$uname");
	} else if (!strcmp($action, "Modify") && $uname) {
		header("Location: modify_user.php?uname=$uname");
	} else if (!strcmp($action, "Delete") && $uname) {
		header("Location: delete_user.php?uname=$uname");
	}
}

$displayUsers = array();
foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	if ($tmpUser->isPseudo()) {
		continue;
	}

	array_push($displayUsers, $tmpUser->getUsername());
}

?>
<html>
<head>
  <title><?php echo _("Manage Users") ?></title>
  <?php echo getStyleSheetLink() ?>
</head>
<body dir=<?php echo $gallery->direction ?>>

<center>
<span class="popuphead"><?php echo _("Manage Users") ?></span>
<br>
<br>

<?php echo makeFormIntro("manage_users.php", array("name" => "manageusers_form")); ?>
<?php echo _("You can create, modify and delete users here.") ?>
<p>

<?php
if (!$displayUsers) {
	print "<i>". _("There are no users!  Create one.") ."</i>";
} else {
?>

<select name=uname size=15 onDblClick='my_submit("Modify")'>

<?php
	foreach ($displayUsers as $name) {
		print "<option value=\"$name\"> $name";
	}
}
?>

</select>

<p>
<input type=button value=<?php echo '"'.  _("Create") .'"' ?> onClick='my_submit("Create")'> 
<?php  if (count($displayUsers)) { ?>
<input type=button value=<?php echo '"'. _("Modify") .'"' ?> onClick='my_submit("Modify")'>
<input type=button value=<?php echo '"'. _("Delete") .'"' ?> onClick='my_submit("Delete")'>
<?php  } ?>
<input type=button value=<?php echo '"'. _("Done") .'"' ?> onclick='parent.close()'>
<input type=hidden name=action value="">
</form>

<script language="javascript1.2">
<!--
// position cursor in top form field
// document.manageusers_form.uname.focus();

function my_submit(action) {
	document.manageusers_form.action.value = action;
	document.manageusers_form.submit();
}
//--> 
</script>

</body>
</html>
