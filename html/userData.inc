<?php /* $Id$ */ ?>
<p>
 <?php 
 if (!isset($uname)) {  $uname=""; }
 if (!isset($old_password)) {  $old_password=""; }
 if (!isset($new_password1)) {  $new_password1=""; }
 if (!isset($new_password2)) {  $new_password2=""; }
 if (!isset($fullname)) {  $fullname=""; }
 if (!isset($email)) {  $email=""; }
 if (!isset($send_email)) {  $send_email=""; }
		 ?>
<table>

   <?php if ($allowChange["member_file"] === true) { ?>
<tr><td><?php echo _("Member file") ?> </td>
<td><input size=30 name="membersfile" type="file"> </td></tr> 
 <?php echo errorRow("membersfile") ?>
<?php } ?>
 <tr>
  <td>
  <?php if ($allowChange["uname"] || $uname) {
	  echo _("Username") ;
  }
?>
  </td>
  <td>
   <?php if ($allowChange["uname"] === true) { ?>
	<input type=text name="uname" value="<?php echo $uname ?>">
   <?php } else { ?>
	<?php echo $uname ?>
	<input type=hidden name="uname" value="<?php echo $uname ?>">
   <?php
	}
   ?>
  </td>
 </tr>

 <?php if ($allowChange["uname"] || $uname) {
	 echo errorRow("uname");
 }
?>

 <?php if ($allowChange["old_password"] === true) { ?>

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

 <?php if ($allowChange["password"] === true) { ?>
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

 <?php } ?>

 <?php if ($allowChange["fullname"] === true) { ?>
 <tr>
  <td>
  <?php echo _("Full Name") ?>
  </td>
  <td>
   <input type=text name="fullname" value="<?php echo $fullname ?>">
  </td>
 </tr>

 <?php echo errorRow("fullname") ?>

 <?php } ?>

 <?php if ($allowChange["email"] === true) { ?>
 <tr>
  <td>
   <?php echo _("Email Address") ?>
  </td>
  <td>
   <input type=text name="email" value="<?php echo $email ?>">
  </td>
 </tr>

 <?php echo errorRow("email") ?>

 <?php } ?>

<?php if ($allowChange["default_language"] === true) {  ?>
	<?php if (!$GALLERY_EMBEDDED_INSIDE && 
			$gallery->app->ML_mode > 1 &&
			sizeof($gallery->app->available_lang) > 1) { ?>
	 <tr> <td> 
		 <?php echo _("Gallery language") ?>
		 </td><td>
    <select name="defaultLanguage" size="1">
    <?php
		$nls = getNLS();
		foreach ($gallery->app->available_lang as $value) {
			// Only use language from config.php if its 
			// defined in nls.php
			if (!isset($nls['language'][$value])) continue;
		
			if ( $defaultLanguage == $value) $selected="selected"; else $selected="";
			echo "\n\t\t<option value=\"$value\" $selected>" 
				. $nls['language'][$value] ."</option>";
		}
?>
	</select>
	</td></tr>
	<?php }  else { ?>
	<input type=hidden name="defaultLanguage" value="<?php echo $gallery->app->default_language ?>">
	<?php } ?>
<?php } ?>





   <?php if ($allowChange["create_albums"] === true) { ?>
 <tr>
  <td>
   <?php echo _("User can create albums") ?>
  </td>
  <td> 

        <?php echo drawSelect("canCreate", $canCreateChoices, $canCreate, 1); ?>
  </td>
 </tr>
<?php } ?>

 <?php if ($allowChange["send_email"] === true && 
		 $gallery->app->emailOn == "yes") { ?>
 <tr>
  <td>
  <?php echo _("Send email to user <br>when account is created") ?>
  </td>
  <td>
	<input type=checkbox name="send_email" checked> 
  </td>
 </tr>
 <?php } else { ?>
	<input type=hidden name="send_email" value="">
 <?php } ?>

<?php if ($gallery->user->isAdmin() && isset($isAdmin)) { ?>
 <tr>
  <td>
   <?php echo _("User is an admin") ?>
  </td>
  <td> 
  <?php if ($allowChange["admin"] === true) { ?>
	  <?php if ($uname=$gallery->user->getUsername() == $uname) { ?>
		  <?php echo $isAdminChoices[$isAdmin] ?> <i>(<?php echo _("you can't un-admin yourself") ?>)</i>
	 <?php } else { ?>
        	<?php echo drawSelect("isAdmin", $isAdminChoices, $isAdmin, 1); ?>
		<br><i>(<?php echo _("Note: admins can always create albums") ?>)</i>
   	<?php } ?>
   <?php } ?>
  </td>
 </tr>
<?php } ?>

</table>

