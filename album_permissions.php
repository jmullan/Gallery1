<?
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
<?
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>
<? require($GALLERY_BASEDIR . "init.php"); ?>
<?
// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	exit;
}
?>
<?
if (isset($allUid) && strchr($submit_read, ">")) {
	$gallery->album->setRead($allUid, 1);
	$changed++;
} else if (isset($readUid) && strchr($submit_read, "<")) {
	$gallery->album->setRead($readUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_text, ">")) {
	$gallery->album->setChangeText($allUid, 1);
	$changed++;
} else if (isset($textUid) && strchr($submit_text, "<")) {
	$gallery->album->setChangeText($textUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_add, ">")) {
	$gallery->album->setAddTo($allUid, 1);
	$changed++;
} else if (isset($addUid) && strchr($submit_add, "<")) {
	$gallery->album->setAddTo($addUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_write, ">")) {
	$gallery->album->setWrite($allUid, 1);
	$changed++;
} else if (isset($writeUid) && strchr($submit_write, "<")) {
	$gallery->album->setWrite($writeUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_delete, ">")) {
	$gallery->album->setDeleteFrom($allUid, 1);
	$changed++;
} else if (isset($deleteUid) && strchr($submit_delete, "<")) {
	$gallery->album->setDeleteFrom($deleteUid, 0);
	$changed++;
}

if (isset($allUid) && strchr($submit_createSub, ">")) {
	$gallery->album->setCreateSubAlbum($allUid, 1);
	$changed++;
} else if (isset($createSubUid) && strchr($submit_createSub, "<")) {
	$gallery->album->setCreateSubAlbum($createSubUid, 0);
	$changed++;
}

if (!strcmp($submit, "Save") && $ownerUid) {
	$gallery->album->setOwner($ownerUid);
	$changed++;
}

if ($changed) {
	$gallery->album->save();
}

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $gallery->userDB->getNobody();
$ownerUid = $nobody->getUid();

$uRead = $gallery->album->getPermUids("canRead");
$uText = $gallery->album->getPermUids("canChangeText");
$uAdd = $gallery->album->getPermUids("canAddTo");
$uWrite = $gallery->album->getPermUids("canWrite");
$uDelete = $gallery->album->getPermUids("canDeleteFrom");
$uCreateSub = $gallery->album->getPermUids("canCreateSubAlbum");

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	$uAll[$uid] = $uname;
}

$owner = $gallery->album->getOwner();
$ownerUid = $owner->getUid();

asort($uRead);
asort($uText);
asort($uWrite);
asort($uDelete);
asort($uCreateSub);
asort($uAdd);
asort($uAll);

correctPseudoUsers($uRead, $ownerUid);
correctPseudoUsers($uText, $ownerUid);
correctPseudoUsers($uWrite, $ownerUid);
correctPseudoUsers($uDelete, $ownerUid);
correctPseudoUsers($uCreateSub, $ownerUid);
correctPseudoUsers($uAdd, $ownerUid);

?>
<html>
<head>
  <title>Album Permissions</title>
  <?= getStyleSheetLink() ?>
</head>
<body>

<center>
<span class="popuphead">Album Permissions</span>
<br>
Changing permissions for <b><?=$gallery->album->fields["title"]?></b>

<?= makeFormIntro("album_permissions.php", 
			array("name" => "albumperms_form")) ?>

<? if ($gallery->user->isAdmin) { ?>
Owner: <?= drawSelect("ownerUid", $uAll, $ownerUid, 1); ?>
<? } ?>

<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td align=center>
   <?= drawSelect("allUid", $uAll, $allUid, 28); ?>
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
      Users who can modify photos.
     </td>
    </tr>
    <tr>
     <td>   
           <input type=submit name="submit_write" value="-->">
      <br> <input type=submit name="submit_write" value="<--">
     </td>
     <td>
      <?= drawSelect("writeUid", $uWrite, $writeUid, 3); ?>
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

    <tr>
     <td colspan=2>
      Users who can create sub albums.
     </td>
    </tr>
    <tr>
     <td>   
           <input type=submit name="submit_createSub" value="-->">
      <br> <input type=submit name="submit_createSub" value="<--">
     </td>
     <td>
      <?= drawSelect("createSubUid", $uCreateSub, $createSubUid, 3); ?>
     </td>
    </tr>

     </table
  </td>
 </tr>
</table>

<input type=submit name="submit" value="Save">
<input type=submit name="submit" value="Done" onclick='parent.close()'>
</form>

</body>
</html>
