<?php /* $Id$ */ ?>
<p>
<table>

 <tr>
  <td>
  <?php echo _("Username") ?>
  </td>
  <td>
   <?php if ($dontChange["uname"]) { ?>
	<?php echo $uname ?>
	<input type=hidden name="uname" value="<?php echo $uname ?>">
   <?php } else { ?>
	<input type=text name="uname" value="<?php echo $uname ?>">
   <?php
	}
   ?>
  </td>
 </tr>

 <?php echo errorRow("uname") ?>

 <?php if ($askForOldPassword) { ?>

 <tr>
  <td>
  <?php echo _("Old Password") ?>
  </td>
  <td>
   <input type=password name="old_password" value="<?php echo $old_password ?>">
  </td>
 </tr>

 <?php echo errorRow("old_password") ?>

 <?php } ?>

 <tr>
  <td>
  <?php echo _("New Password") ?>
  </td>
  <td>
   <input type=password name="new_password1" value="<?php echo $new_password1 ?>">
  </td>
 </tr>

 <?php echo errorRow("new_password1") ?>

 <tr>
  <td>
  <?php echo _("New Password (again)") ?>
  </td>
  <td>
   <input type=password name="new_password2" value="<?php echo $new_password2 ?>">
  </td>
 </tr>

 <?php echo errorRow("new_password2") ?>

 <tr>
  <td>
  <?php echo _("Full Name") ?>
  </td>
  <td>
   <input type=text name="fullname" value="<?php echo $fullname ?>">
  </td>
 </tr>

 <?php echo errorRow("fullname") ?>

 <tr>
  <td>
   <?php echo _("Email Address") ?>
  </td>
  <td>
   <input type=text name="email" value="<?php echo $email ?>">
  </td>
 </tr>

 <?php echo errorRow("email") ?>

<?php
	if (!$GALLERY_EMBEDDED_INSIDE && $gallery->app->ML_mode > 1) {
		?>
			<tr> <td> <?php echo _("Gallery language") ?>
			</td><td>
    <select name="defaultLanguage" size="1">
    <?php
		$nls = getNLS();
		foreach ($gallery->app->available_lang as $value) {
			// Only use language from config.php if its defined in nls.php
			if (! isset($nls['language'][$value])) continue;
		
			if (!language_exists($value)) {
				continue;
			}
			if ( $defaultLanguage == $value) $selected="selected"; else $selected="";
			echo "\n\t\t<option value=\"$value\" $selected>" 
				. $nls['language'][$value] ."</option>";
		}
?>
	</select>
	</td></tr>
	<?php } ?>






<?php if ($gallery->user->isAdmin() && isset($canCreate)) { ?>
 <tr>
  <td>
   <?php echo _("User can create albums") ?>
  </td>
  <td> 
   <?php if ($dontChange["create-albums"]) { ?>
	<?php echo $canCreateChoices[$canCreate] ?> <i>(<?php echo _("admins can always create albums") ?>)</i>
   <?php } else { ?>
        <?php echo drawSelect("canCreate", $canCreateChoices, $canCreate, 1); ?>
   <?php } ?>
  </td>
 </tr>
<?php } ?>

<?php if ($gallery->user->isAdmin() && isset($isAdmin)) { ?>
 <tr>
  <td>
   <?php echo _("User is an admin") ?>
  </td>
  <td> 
   <?php if ($dontChange["admin"]) { ?>
	<?php echo $isAdminChoices[$isAdmin] ?> <i>(<?php echo _("you can't un-admin yourself") ?>)</i>
   <?php } else { ?>
        <?php echo drawSelect("isAdmin", $isAdminChoices, $isAdmin, 1); ?>
   <?php } ?>
  </td>
 </tr>
<?php } ?>

</table>

