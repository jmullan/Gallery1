<?php /* $Id$ */ ?>
<p><?php 
 if (!isset($uname)) {  $uname=""; }
 if (!isset($old_password)) {  $old_password=""; }
 if (!isset($new_password1)) {  $new_password1=""; }
 if (!isset($new_password2)) {  $new_password2=""; }
 if (!isset($fullname)) {  $fullname=""; }
 if (!isset($email)) {  $email=""; }
 if (!isset($send_email)) {  $send_email=""; }

 $hidden=array();
?>
</p>

<table>
<?php if ($allowChange["member_file"] === true) { ?>
<tr>
	<td><?php echo _("Member file") ?></td>
	<td><input name="membersfile" size="30" type="file"></td>
</tr>
<?php echo errorRow("membersfile");
} 
?>
<tr>
<td><?php if ($allowChange["uname"] || $uname) {
	  echo _("Username") ;
}
?></td>
<td><?php 
if ($allowChange["uname"] === true) {
	echo "\n". '<input type="text" name="uname" value="'. $uname .'">';
} else {
	echo $uname;
	$hidden[]=array('uname', $uname);
}
?></td>
</tr>
<?php if ($allowChange["uname"] || $uname) {
	 echo errorRow("uname");
}
if ($allowChange["old_password"] === true) { ?>
<tr>
	<td><?php echo _("Old Password") ?></td>
	<td><input name="old_password" type="password" value="<?php echo $old_password ?>"></td>
</tr>
<?php 	echo errorRow("old_password");
}
if ($allowChange["password"] === true) { ?>
<tr>
	<td><?php echo _("New Password") ?></td>
	<td><input name="new_password1" type="password" value="<?php echo $new_password1 ?>"></td>
</tr>
<?php 	echo errorRow("new_password1") ?>
<tr>
	<td><?php echo _("New Password (again)") ?></td>
	<td><input name="new_password2" type="password" value="<?php echo $new_password2 ?>"></td>
</tr>
<?php 	echo errorRow("new_password2");
}
if ($allowChange["fullname"] === true) { ?>
<tr>
	<td><?php echo _("Full Name") ?></td>
	<td><input name="fullname" type="text" value="<?php echo $fullname ?>"></td>
</tr>
<?php echo errorRow("fullname");
}
if ($allowChange["email"] === true) { ?>
<tr>
	<td><?php echo _("Email Address") ?></td>
	<td><input name="email" type="text" value="<?php echo $email ?>"></td>
</tr>
<?php echo errorRow("email");
}
if (isset($allowChange["default_language"]) && $allowChange["default_language"]) {
	if (!$GALLERY_EMBEDDED_INSIDE && 
		$gallery->app->ML_mode > 1 &&
		sizeof($gallery->app->available_lang) > 1) { ?>
<tr>
	<td><?php echo _("Gallery language") ?></td>
	<td><select name="defaultLanguage" size="1">
<?php
		$nls = getNLS();
		$selectedSet = false;
		foreach ($gallery->app->available_lang as $value) {
			// Only use language from config.php if its 
			// defined in nls.php
			if (!isset($nls['language'][$value])) continue;
		
			if ( $selectedSet == false) {
				if (
				(isset($defaultLanguage) && $defaultLanguage == $value) ||
				(isset($gallery->app->default_language) && 
					$gallery->app->default_language == $value)) {
					$selected="selected";
					$selectedSet = true;
				}
			} else {
				$selected="";
			}
			echo "\n\t\t<option value=\"$value\" $selected>" 
				. $nls['language'][$value] ."</option>";
		}
?>
</select></td>
</tr>
<?php 	}  
	else { 
		$hidden[]=array('defaultLanguage', $gallery->app->default_language);
	}
}
if (isset($allowChange["create_albums"]) && $allowChange["create_albums"] === true) { ?>
<tr>
	<td><?php echo _("User can create albums") ?></td>
	<td><?php echo drawSelect("canCreate", $canCreateChoices, $canCreate, 1); ?></td>
</tr>
<?php 
}
if ($allowChange["send_email"] === true && 
	$gallery->app->emailOn == "yes") { ?>
<tr>
	<td><?php echo _("Send email to user <br>when account is created") ?></td>
	<td><input checked name="send_email" type="checkbox"></td>
</tr>
<?php 
} else { 
	$hidden[]=array('send_email', "");
}
if ($gallery->user->isAdmin() && isset($isAdmin)) { ?>
<tr>
	<td><?php echo _("User is an admin") ?></td>
	<td><?php 
		if ($allowChange["admin"] === true) {
			if ($uname=$gallery->user->getUsername() == $uname) {
				echo $isAdminChoices[$isAdmin] . ' <i>( '. _("you can't un-admin yourself") . ')</i>';
			} else {
				echo drawSelect("isAdmin", $isAdminChoices, $isAdmin, 1);
				echo '<br>';
				echo '<i>('. _("Note: admins can always create albums") . ')</i>';
			}
		} ?></td>
</tr>
<?php 
} ?>
</table>
<?php
	foreach ($hidden as $line) {
		echo "\n". '<input type="hidden" name="'. $line[0] .'" value="'. $line[1] .'">';
	}
	echo "\n";
?>
