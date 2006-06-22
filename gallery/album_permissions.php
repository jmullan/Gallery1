<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($save, $ownerUid, $submit, $actionUid) =
	getRequestVar(array('save', 'ownerUid', 'submit', 'actionUid'));

// Hack check
if (!$gallery->user->isAdmin() && 
    !$gallery->user->isOwnerOfAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$perms = array(
    'canRead'           => gTranslate('core', "Users who can see the album."),
    'canAddTo'          => gTranslate('core', "Users who can add photos."),
    'canDeleteFrom'     => gTranslate('core', "Users who can delete photos."),
    'canWrite'          => gTranslate('core', "Users who can modify photos."),
    'canCreateSubAlbum' => gTranslate('core', "Users who can create sub albums."),
    'zipDownload'       => gTranslate('core', "Users who can to download album (with subalbums) as archive."),
    'canViewComments'   => gTranslate('core', "Users who can view comments."),
    'canAddComments'    => gTranslate('core', "Users who can add comments."),
    'canViewFullImages' => gTranslate('core', "Users who can view full (original) images."),
    'canChangeText'     => gTranslate('core', "Users who can change album text."),
);

foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	$uname = $tmpUser->getUsername();
	if ($tmpUser->isPseudo()) {
	    $uname = "*$uname*";
	}
	$uAll[$uid] = $uname;
}

asort($uAll);

$changed = 0;
if(empty($submit)) {
    $submit = array();
}

foreach ($submit as $perm => $action) {
    if(isset($action) && isset($actionUid)) {
        $action = unhtmlentities($action);
        if($action == '-->') {
            $gallery->album->setPerm($perm, $actionUid, true);
            $changed++;
        }
        if($action == '<--') {
            $gallery->album->setPerm($perm, $actionUid, false);
            $changed++;
        }
    }
}

if (isset($save) && $ownerUid) {
    $gallery->album->setOwner($ownerUid);
    $changed++;
}

if ($changed) {
    $gallery->album->save(array(i18n("Permissions have been changed")));
    if (getRequestVar('setNested')) {
        $gallery->album->setNestedPermissions();
    }
}

// Start with a default owner of nobody -- if there is an
// owner it'll get filled in below.
$nobody = $gallery->userDB->getNobody();
$ownerUid = $nobody->getUid();

$owner = $gallery->album->getOwner();
$ownerUid = $owner->getUid();

// Set values for selectboxes
foreach($perms as $perm => $trash)  {
    $uids[$perm] = $gallery->album->getPermUids($perm);
    asort($uids[$perm]);
    correctPseudoUsers($uids[$perm], $ownerUid);
}

printPopupStart(gTranslate('core', "Album Permissions"));

echo sprintf(gTranslate('core', "Changing permissions for %s"), '<b>'.$gallery->album->fields["title"] . '</b>');

echo makeFormIntro("album_permissions.php", array("name" =>
			"albumperms_form"), array("type" => "popup"));

if ($gallery->user->isAdmin) {
    echo gTranslate('core', "Owner:") . drawSelect("ownerUid", $uAll, $ownerUid, 1, array(), true);
}
?>

<table border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td align="center">
   <?php echo drawSelect('actionUid', $uAll, isset($allUid) ? $allUid : array(), 28, array(), true); ?>
  </td>

  <td>&nbsp;</td>

  <td style="vertical-align: top">
<?php

$permsTable = new galleryTable();
$permsTable->setColumnCount(2);
foreach($perms as $perm => $permDesc) {
    $permsTable->addElement(array('content' => $permDesc, 'cellArgs' => array('colspan' => 2)));
    $permsTable->addElement(
	   array('content' => 
	    "\n\t<input type=\"submit\" name=\"submit[$perm]\" value=\"-->\"><br>".
	    "\n\t<input type=\"submit\" name=\"submit[$perm]\" value=\"<--\">"
	));
    $permsTable->addElement(
	array('content' => drawSelect("actionUid", $uids[$perm], '', 3, array(), true))
    );
}
echo $permsTable->render();
?>    
  </td>
 </tr>
</table>

<label for="setNested"><?php echo gTranslate('core', "Apply permissions to all sub-albums"); ?></label>
<input type="checkbox" id="setNested" name="setNested" value="setNested" <?php if (getRequestVar('setNested')) echo 'CHECKED'; ?>>
<br><br>
<?php echo gSubmit('save', gTranslate('core', "_Save")); ?>
<?php echo gButton('done', gTranslate('core', "_Done")), 'parent.close()'); ?>
</form>
</div>
<?php print gallery_validation_link("album_permissions.php"); ?>
</body>
</html>
