<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

list($create, $bulk_create, $modify, $delete, $unlock, $unames) =
	getRequestVar(array('create', 'bulk_create', 'modify', 'delete', 'unlock', 'unames'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Manage Users"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	includeHtmlWrap("popup.footer");
	exit;
}

require_once(dirname(__FILE__) . '/classes/Logins.php');

$notice_messages = array();

$userLogins = new Logins();
$userLogins->load();

if (!empty($create)) {
	header('Location: ' . makeGalleryHeaderUrl('create_user.php', array('type' => 'popup')));
}
if (!empty($bulk_create)) {
	header('Location: ' . makeGalleryHeaderUrl('multi_create_user.php', array('type' => 'popup')));
}

if ( (isset($modify) || isset($delete) || isset($unlock)) && ! isset($unames)) {
	$notice_messages[] = array('type' => 'error', 'text' => gTranslate('core', "Please select a user"));
}
elseif (isset($modify)) {
	header('Location: ' . makeGalleryHeaderUrl('modify_user.php', array('uname' => $unames[0], 'type' => 'popup')));
}
elseif (isset($delete)) {
	header('Location: ' . makeGalleryHeaderUrl('delete_user.php', array('unames' => $unames, 'type' => 'popup')));
}
elseif(isset($unlock)) {
	$userLogins->reset($unames);
	$userLogins->save();
}

$displayUsers = array();
foreach ($gallery->userDB->getUidList() as $uid) {
	$tmpUser = $gallery->userDB->getUserByUid($uid);
	if ($tmpUser->isPseudo()) {
		continue;
	}

	$tmpUserName = $tmpUser->getUsername();
	$tmpUserEmail = $tmpUser->getEmail();
	if(empty($tmpUserEmail)) {
		$tmpUserEmail = gTranslate('core', "&lt;No email set&gt;");
	}

	$isAdmin = $tmpUser->isAdmin() ? gTranslate('core', "yes") : gTranslate('core', "no");

	$tooltip = '<table class=g-tooltip><tr>' .
			'<td>' . gTranslate('core', "Username") ."</td><td>:</td><td>$tmpUserName</td>" .
			'</tr><tr>' .
			'<td>' . gTranslate('core', "Full name") ."</td><td>:</td><td>" . $tmpUser->getFullname() ."</td>" .
			'</tr><tr>' .
			'<td>' . gTranslate('core', "Email") ."</td><td>:</td><td>$tmpUserEmail</td>" .
			'</tr><tr>' .
			'<td>' . gTranslate('core', "Admin") ."</td><td>:</td><td>$isAdmin</td>";

	if($userLogins->userIslocked($tmpUserName)) {
		$tooltip .= '</tr><tr>';
		$tooltip .= "<td colspan=3 class=center><b>" . gTranslate('core', "Account is locked!") . "</b></td>";
		$locked = true;
	}

	$tooltip .= '</tr></table>';

	$displayUsers[] = array(
		'value'	=> $tmpUserName,
		'text'	=> $tmpUserName,
		'onmouseover' => "Tip('$tooltip', FADEIN, 600, FADEOUT, 300, OPACITY, 90)",
		'onmouseout' => "UnTip()",
		'class' => $userLogins->userIslocked($tmpUserName) ? 'g-locked' : false
	);

}

asort($displayUsers);

doctype();
?>
<html>
<head>
  <title><?php echo gTranslate('core', "Manage Users"); ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody" onload="enableButtons()">
<?php echo jsHTML('wz/wz_tooltip.js'); ?>
<div class="popuphead"><?php echo gTranslate('core', "Manage Users"); ?></div>
<div class="popup" align="center">
<?php
echo infoBox($notice_messages);

echo gTranslate('core', "You can create, modify and delete users here.");

echo makeFormIntro('manage_users.php', array(), array('type' => 'popup'));

if (!$displayUsers) {
	echo "<i>". gTranslate('core', "There are no users!  Create one.") ."</i>";
}
else {
	echo drawSelect2('unames[]', $displayUsers,
	   array('size' => 15,
	   		 'id'	=> 'userNameBox',
			 'onChange' => 'enableButtons()',
			 'multiple' => null)
	);
}

echo "\n<br>";
echo gTranslate('core', "To select multiple users (only recognized for deletion), hold down the Control (PC) or Command (Mac) key while clicking.");

echo "\n<br><br>";

echo gSubmit('create', gTranslate('core', "Create new user"));
if ($gallery->app->multiple_create == "yes") {
	echo gSubmit('bulk_create', gTranslate('core', "Bulk Create"));
}
if (count($displayUsers)) {
	echo gSubmit('modify', gTranslate('core', "Modify"));
	echo gSubmit('delete', gTranslate('core', "Delete"));
	if(isset($locked)) {
		echo gSubmit('unlock', gTranslate('core', "Unlock"));
	}
}
echo gButton('done', gTranslate('core', "Done"), 'parent.close()');
?>
</form>

<script type="text/javascript">
	var userNameBox = document.getElementById('userNameBox');
	var userCount = userNameBox.length;

	var createButton = document.getElementById('create');
	var modifyButton = document.getElementById('modify');
	var deleteButton = document.getElementById('delete');
	var unlockButton = document.getElementById('unlock');
	var doneButton   = document.getElementById('done');

	function enableButtons() {
		var selected = 0;

		if(unlockButton) {
			unlockButton.disabled	= true;
			unlockButton.className	= 'g-buttonDisable';
		}

		for (i = 0; i < userCount; i++) {
			if(userNameBox.options[i].selected) {
				selected++;
				if(userNameBox.options[i].className == 'g-locked') {
					unlockButton.disabled	= false;
					unlockButton.className	= 'g-button';
				}
			}
		}

		if(selected == 0) {
			modifyButton.disabled	= true;
			modifyButton.className	= 'g-buttonDisable';
			deleteButton.disabled	= true;
			deleteButton.className	= 'g-buttonDisable';
		}
		else if (selected > 1) {
			modifyButton.disabled	= true;
			modifyButton.className	= 'g-buttonDisable';
		}
		else {
			modifyButton.disabled	= false;
			modifyButton.className	= 'g-button';
			deleteButton.disabled	= false;
			deleteButton.className	= 'g-button';
		}

	}
</script>

<?php includeHtmlWrap("popup.footer"); ?>

</body>
</html>