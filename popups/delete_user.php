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

require_once(dirname(dirname(__FILE__)) . '/init.php');

list($formaction, $unames) = getRequestVar(array('formaction', 'unames'));

if (!$gallery->user->isAdmin()) {
	printPopupStart(gTranslate('core', "Delete User"));
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

if (isset($formaction) && $formaction == 'delete') {
	foreach($unames as $user) {
		$gallery->userDB->deleteUserByUsername($user);
	}
}

if (!empty($formaction)) {
	header('Location: '. makeGalleryHeaderUrl("manage_users.php", array('type' => 'popup')));
}

printPopupStart(gTranslate('core', "Delete User"), '', 'left');

foreach ($unames as $key => $user) {
	if ($gallery->user->getUsername() == $user) {
		echo gallery_info(gTranslate('core', "You can't delete your own account! It was removed from the list."));
		unset($unames[$key]);
	}
}

if (! empty($unames)) {
	echo "\n<p>". gTranslate('core', "Users can have special permissions in each album.") .
	gTranslate('core', "If you delete this user, any such permissions go away.", "If you delete these users, any permissions will go away.", sizeof($unames)) .
	gTranslate('core', "Deleted users cannot be recovered.") .
	gTranslate('core', "Even if this user is recreated, those permissions are gone.", "Even if you recreate one of those users, the permissions are gone.", sizeof($unames));
}

echo "\n<center>";
echo makeFormIntro('delete_user.php',
	array('name' => 'deleteuser_form',
	      'onsubmit' => "deleteuser_form.deleteButton.disabled='true'"),
	array('type' => 'popup')
);

if (! empty($unames)) {
	echo gTranslate('core', "Do you really want to delete user:", "Do you really want to delete these users:", sizeof($unames));
	foreach ($unames as $key => $value) {
		echo "<input type=\"hidden\" name=\"unames[$key]\" value=\"$value\"><br>$value\n";
	}
?>
<br><br>
<input type="submit" name="deleteButton" value="<?php echo gTranslate('core', "Delete") ?>" onclick="deleteuser_form.formaction.value='delete'" class="g-button">
<?php
}
else {
	echo gTranslate('core', "No user available for deletion.");
}
?>
<input type="hidden" name="formaction" value="">
<input type="submit" name="cancel" value="<?php echo gTranslate('core', "Back to usermanagement") ?>" onclick="deleteuser_form.formaction.value='back'" class="g-button">
</form>
</center>

<?php includeTemplate('overall.footer'); ?>

</body>
</html>
