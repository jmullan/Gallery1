<?
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000 Bharat Mediratta
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
<? require('init.php'); ?>
<?
if (isset($allUid) && strchr($submit_read, ">")) {
	$album->setRead($allUid, 1);
	$changed++;
} else if (isset($readUid) && strchr($submit_read, "<")) {
	$album->setRead($readUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_text, ">")) {
	$album->setChangeText($allUid, 1);
	$changed++;
} else if (isset($textUid) && strchr($submit_text, "<")) {
	$album->setChangeText($textUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_add, ">")) {
	$album->setAddTo($allUid, 1);
	$changed++;
} else if (isset($addUid) && strchr($submit_add, "<")) {
	$album->setAddTo($addUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_delete, ">")) {
	$album->setDeleteFrom($allUid, 1);
	$changed++;
} else if (isset($deleteUid) && strchr($submit_delete, "<")) {
	$album->setDeleteFrom($deleteUid, 0);
	$changed++;
}

if (!strcmp($submit, "Save") && $ownerUid) {
	$album->setOwner($ownerUid);
	$changed++;
}

if ($changed) {
	$album->save();
}

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $userDB->getNobody();
$ownerUid = $nobody->getUid();

$uRead = array();
$uText = array();
$uAdd = array();
$uDelete = array();

foreach ($userDB->getUidList() as $uid) {
	$tmpUser = $userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();

	// Skip the admin user
	if ($tmpUser->isAdmin()) {
		continue;
	}
	
	$uAll[$uid] = $uname;

	if ($album->isOwner($uid)) {
		$ownerUid = $uid;
	}

	if ($album->canRead($uid)) {
		$uRead[$uid] = $uname;
	}

	if ($album->canChangeText($uid)) {
		$uText[$uid] = $uname;
	}

	if ($album->canAddTo($uid)) {
		$uAdd[$uid] = $uname;
	}

	if ($album->canDeleteFrom($uid)) {
		$uDelete[$uid] = $uname;
	}
}

asort($uRead);
asort($uText);
asort($uDelete);
asort($uAdd);
asort($uAll);

correctNobody(&$uRead);
correctNobody(&$uText);
correctNobody(&$uDelete);
correctNobody(&$uAdd);

correctEverybody(&$uRead);
correctEverybody(&$uText);
correctEverybody(&$uDelete);
correctEverybody(&$uAdd);

?>
<html>
<head>
  <title>Album Permissions</title>
  <link rel="stylesheet" type="text/css" href="<?= getGalleryStyleSheetName() ?>">
</head>
<body>

<center>
<span class="popuphead">Album Permissions</span>
<br>
Changing permissions for <b><?=$album->fields["title"]?></b>
<br>

<form name=albumperms_form method=GET>

<? if ($user->isAdmin) { ?>
Owner: <?= drawSelect("ownerUid", $uAll, $ownerUid, 1, $uNobody); ?>
<? } ?>

<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td align=center>
   <?= drawSelect("allUid", $uAll, $allUid, 18, $uPubUser); ?>
  </td>

  <td> &nbsp; </td>

  <td valign=top>
   <table border=0 cellspacing=3 cellpadding=0>
    <tr>
     <td colspan=2>
      Users who can see the album
     </td>
    </tr>
    <tr>
     <td>   
           <input type=submit name="submit_read" value="-->">
      <br> <input type=submit name="submit_read" value="<--">
     </td>
     <td align=left>
      <?= drawSelect("readUid", $uRead, $readUid, 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan=2>
      Users who can change album text.
     </td>
    </tr>
    <tr>
     <td>
           <input type=submit name="submit_text" value="-->">
      <br> <input type=submit name="submit_text" value="<--">
     </td>
     <td>
      <?= drawSelect("textUid", $uText, $textUid, 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan=2>
      Users who can add photos.
     </td>
    </tr>
    <tr>
     <td>   
           <input type=submit name="submit_add" value="-->">
      <br> <input type=submit name="submit_add" value="<--">
     </td>
     <td>
      <?= drawSelect("addUid", $uAdd, $addUid, 3); ?>
     </td>
    </tr>

    <tr>
     <td colspan=2>
      Users who can delete photos.
     </td>
    </tr>
    <tr>
     <td>   
           <input type=submit name="submit_delete" value="-->">
      <br> <input type=submit name="submit_delete" value="<--">
     </td>
     <td>
      <?= drawSelect("deleteUid", $uDelete, $deleteUid, 3); ?>
     </td>
    </tr>

   </table
  </td>
 </tr>
</table>

<br>

<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Done" onclick='parent.close()'>
</form>

</body>
</html>
