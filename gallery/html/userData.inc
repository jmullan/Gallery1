<!-- $Id$ -->
<p>
<table>

 <tr>
  <td>
   Username
  </td>
  <td>
   <?php if ($dontChange["uname"]) { ?>
	<?php echo $uname ?>
	<input type=hidden name="uname" value="<?php echo $uname?>">
   <?php } else { ?>
	<input type=text name="uname" value="<?php echo $uname?>">
   <?php
	}
   ?>
  </td>
 </tr>

 <?php echo errorRow("uname") ?>

 <?php if ($askForOldPassword) { ?>

 <tr>
  <td>
   Old Password
  </td>
  <td>
   <input type=password name="old_password" value="<?php echo $old_password?>">
  </td>
 </tr>

 <?php echo errorRow("old_password") ?>

 <?php } ?>

 <tr>
  <td>
   New Password
  </td>
  <td>
   <input type=password name="new_password1" value="<?php echo $new_password1?>">
  </td>
 </tr>

 <?php echo errorRow("new_password1") ?>

 <tr>
  <td>
   New Password (again)
  </td>
  <td>
   <input type=password name="new_password2" value="<?php echo $new_password2?>">
  </td>
 </tr>

 <?php echo errorRow("new_password2") ?>

 <tr>
  <td>
   Full Name
  </td>
  <td>
   <input type=text name="fullname" value="<?php echo $fullname?>">
  </td>
 </tr>

 <?php echo errorRow("fullname") ?>

 <tr>
  <td>
   Email Address
  </td>
  <td>
   <input type=text name="email" value="<?php echo $email?>">
  </td>
 </tr>

 <?php echo errorRow("email") ?>

<?php if ($gallery->user->isAdmin() && isset($canCreate)) { ?>
 <tr>
  <td>
   User can create albums
  </td>
  <td> 
   <?php if ($dontChange["create-albums"]) { ?>
	<?php echo $canCreateChoices[$canCreate] ?> <i>(admins can always create albums)</i>
   <?php } else { ?>
        <?php echo drawSelect("canCreate", $canCreateChoices, $canCreate, 1); ?>
   <?php } ?>
  </td>
 </tr>
<?php } ?>

<?php if ($gallery->user->isAdmin() && isset($isAdmin)) { ?>
 <tr>
  <td>
   User is an admin
  </td>
  <td> 
   <?php if ($dontChange["admin"]) { ?>
	<?php echo $isAdminChoices[$isAdmin] ?> <i>(you can't un-admin yourself)</i>
   <?php } else { ?>
        <?php echo drawSelect("isAdmin", $isAdminChoices, $isAdmin, 1); ?>
   <?php } ?>
  </td>
 </tr>
<?php } ?>

</table>
