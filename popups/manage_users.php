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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($create, $bulk_create, $modify, $delete, $unames) =
    getRequestVar(array('create', 'bulk_create', 'modify', 'delete', 'unames'));

if (!$gallery->user->isAdmin()) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$notice_messages = array();

if (!empty($create)) {
	header('Location: ' . makeGalleryHeaderUrl('create_user.php', array('type' => 'popup')));
}
if (!empty($bulk_create)) {
	header('Location: ' . makeGalleryHeaderUrl('multi_create_user.php', array('type' => 'popup')));
}

if ( (isset($modify) || isset($delete)) && ! isset($unames)) {
	$notice_messages[] = array('type' => 'error', 'text' => gTranslate('core', "Please select a user"));
}
elseif (isset($modify)) {
	header('Location: ' . makeGalleryHeaderUrl('modify_user.php', array('uname' => $unames[0], 'type' => 'popup')));
}
elseif (isset($delete)) {
	header('Location: ' . makeGalleryHeaderUrl('delete_user.php', array('unames' => $unames, 'type' => 'popup')));
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
  <title><?php echo gTranslate('core', "Manage Users"); ?></title>
  <?php common_header(); ?>
</head>
<body class="g-popup" onload="enableButtons()">
<div class="g-header-popup">
  <div class="g-pagetitle-popup"><?php echo gTranslate('core', "Manage Users"); ?></div>
</div>
<div class="g-content-popup center">

<?php
echo infoBox($notice_messages);

echo gTranslate('core', "You can create, modify and delete users here.");

echo makeFormIntro('manage_users.php', array(), array('type' => 'popup'));

if (!$displayUsers) {
	echo "<i>". gTranslate('core', "There are no users!  Create one.") ."</i>";
}
else {
	echo drawSelect('unames[]', $displayUsers, '', 15,
	   array('id' => 'userNameBox',
	         'multiple' => '',
	         'onChange' => 'enableButtons()')
    );
}

echo "\n<br>";
echo gTranslate('core', "To select multiple users (only recognized for deletion), hold down the Control (PC) or Command (Mac) key while clicking.");

echo "\n<br><br>";

echo gSubmit('create', gTranslate('core', "Create _new user"));
if ($gallery->app->multiple_create == "yes") {
    echo gSubmit('bulk_create', gTranslate('core', "_Bulk Create"));
}
if (count($displayUsers)) {
    echo gSubmit('modify', gTranslate('core', "_Modify"));
    echo gSubmit('delete', gTranslate('core', "_Delete"));
}
echo gButton('done', gTranslate('core', "_Done"), 'parent.close()');
?>
</form>

</div>

<script type="text/javascript">
    var userNameBox = document.getElementById('userNameBox');
    var userCount = userNameBox.length;

    var createButton = document.getElementById('create');
    var modifyButton = document.getElementById('modify');
    var deleteButton = document.getElementById('delete');
    var doneButton   = document.getElementById('done');

    function enableButtons() {
        var selected = 0;
        for (i = 0; i < userCount; i++) {
            if(userNameBox.options[i].selected) {
                selected++;
            }
        }

        if(selected == 0) {
            modifyButton.disabled = true;
            modifyButton.className = 'g-buttonDisable';
            deleteButton.disabled = true;
            deleteButton.className = 'g-buttonDisable';

        }
        else if (selected > 1) {
            modifyButton.disabled = true;
            modifyButton.className = 'g-buttonDisable';
        }
        else {
            modifyButton.disabled = false;
            modifyButton.className = 'g-button';
            deleteButton.disabled = false;
            deleteButton.className = 'g-button';
        }

    }
</script>
</body>
</html>
