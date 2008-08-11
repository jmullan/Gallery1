<?php
	/* $Id$ */

if (!isset($uname)) { $uname = ''; }
if (!isset($old_password)) { $old_password = ''; }
if (!isset($new_password1)) { $new_password1 = ''; }
if (!isset($new_password2)) { $new_password2 = ''; }
if (!isset($fullname)) { $fullname = ''; }
if (!isset($email)) { $email = ''; }
if (!isset($send_email)) { $send_email = ''; }
if (!isset($isAdmin)) { $isAdmin = 0; }

$hidden = array();
$yesNoChoices = array(1 => gTranslate('core', "Yes"), 0 => gTranslate('core', "No"));

$nls = getNLS();
$languages = array();
$selected = $gallery->app->default_language;

if(!empty($gallery->app->available_lang)) {
	foreach ($gallery->app->available_lang as $value) {
		// Only use language from config.php if its
		// defined in nls.php
		if (!isset($nls['language'][$value])) continue;

		if (isset($defaultLanguage) && $defaultLanguage == $value) {
			$selected = $defaultLanguage;
		}

		$languages[$value] = $nls['language'][$value];
		ksort($languages);
	}
}
else {
	$languages[$selected] = $nls['language'][$selected];
}

?>
<table class="left">
<?php if ($allowChange['member_file'] === true) { ?>
<tr>
	<td colspan="2">
	<?php echo gTranslate('core', "Member file") ?> <input name="membersfile" size="30" type="file">
	</td>
</tr>
<?php echo errorRow("membersfile");
}

if ($allowChange['uname'] || $uname) {
	if ($allowChange['uname'] === true) {
		echo gInput('text', 'uname', gTranslate('core', "Username"), true, $uname, array('class' => 'g-usernameInput'));
}
else {
	echo gInput('fixedhidden', 'uname', gTranslate('core', "Username"), true, $uname);
	$hidden[] = array('uname', $uname);
}
	echo errorRow("uname");
}

if (isset($allowChange['password']) && $allowChange['password'] === true) {
	if (isset($allowChange['old_password']) && $allowChange['old_password'] === true) {
		echo gInput('password', 'old_password', gTranslate('core', "Old Password"), true, '', array('class' => 'g-passwordInput'));
		echo errorRow("old_password");
	}

	echo gInput('password', 'new_password1', gTranslate('core', "New Password"), true, '', array('class' => 'g-passwordInput', 'addCheck' => true));
	echo errorRow("new_password1");

	echo gInput('password', 'new_password2', gTranslate('core', "New Password (again)"), true, '', array('class' => 'g-passwordInput'));
	echo errorRow("new_password2");
}


if ($allowChange['fullname'] === true) {
	echo gInput('text', 'fullname', gTranslate('core', "Full Name"), true, $fullname, array('size' => 25));
	echo errorRow("fullname");
}

if ($allowChange['email'] === true) {
	echo gInput('text', 'email', gTranslate('core', "Email Address"), true, $email, array('size' => 35));
	echo errorRow("email");
}

if (isset($allowChange['default_language']) && $allowChange['default_language'] === true) {
	if (!$GALLERY_EMBEDDED_INSIDE && $gallery->app->ML_mode > 1) { ?>
<tr>
	<td><label for="defaultLanguage"><?php echo gTranslate('core', "Gallery language") ?></label></td>
	<td><?php echo drawSelect('defaultLanguage', $languages, $selected,1,array('id' => 'defaultLanguage')); ?>
</tr>
<?php 	}
}

if (isset($allowChange['canChangeOwnPw']) && $allowChange['canChangeOwnPw'] === true) { ?>
<tr>
        <td><?php echo gTranslate('core', "User can change its own password?") ?></td>
        <td><?php echo drawSelect("canChangeOwnPw", $yesNoChoices, $canChangeOwnPw, 1); ?></td>
</tr>
<?php
}

if (isset($allowChange['create_albums']) && $allowChange['create_albums'] === true) { ?>
<tr>
	<td><?php echo gTranslate('core', "User can create albums") ?></td>
	<td><?php echo drawSelect("canCreate", $yesNoChoices, $canCreate, 1); ?></td>
</tr>
<?php
}

if ($gallery->user->isAdmin()) { ?>
<tr>
	<td><?php echo gTranslate('core', "User is an admin") ?></td>
	<?php
	if (isset($allowChange['admin']) && $allowChange['admin'] === true) {
		    if ($uname=$gallery->user->getUsername() == $uname) {
				echo '<td>'. $yesNoChoices[$isAdmin] . "</td></tr>\n";
				echo '<tr><td colspan="2"><i>('. gTranslate('core', "You can't un-admin yourself.") . ')</i></td>';
				$hidden[] = array('isAdmin', $isAdmin);
		    }
		    else {
			echo '<td>'. drawSelect("isAdmin", $yesNoChoices, $isAdmin, 1) . "</td></tr>\n";
			echo '<tr><td colspan="2"><i>('. gTranslate('core', "Note: Admins can always create albums and are allowed to change their password.") .')</i></td>';
		    }
	} ?>
</tr>
<?php
}

if ($allowChange['send_email'] === true && $gallery->app->emailOn == "yes") { ?>
<tr>
	<td><?php echo gTranslate('core', "Send email to user when account is created") ?></td>
	<td><input name="send_email" type="checkbox"></td>
</tr>
<?php
}
else {
	$hidden[] = array('send_email', "");
}
?>
</table>
<?php
foreach ($hidden as $line) {
	echo "\n". '<input type="hidden" name="'. $line[0] .'" value="'. $line[1] .'">';
}
echo "\n";
?>
