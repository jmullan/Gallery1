<p>
<table>

 <tr>
  <td>
   Username
  </td>
  <td>
   <? if ($dontChange["uname"]) { ?>
	<?= $uname ?>
	<input type=hidden name="uname" value="<?=$uname?>">
   <? } else { ?>
	<input type=text name="uname" value="<?=$uname?>">
   <?
	}
   ?>
  </td>
 </tr>

 <?= errorRow("uname") ?>

 <? if ($askForOldPassword) { ?>

 <tr>
  <td>
   Old Password
  </td>
  <td>
   <input type=password name="old_password" value="<?=$old_password?>">
  </td>
 </tr>

 <?= errorRow("old_password") ?>

 <? } ?>

 <tr>
  <td>
   New Password
  </td>
  <td>
   <input type=password name="new_password1" value="<?=$new_password1?>">
  </td>
 </tr>

 <?= errorRow("new_password1") ?>

 <tr>
  <td>
   New Password (again)
  </td>
  <td>
   <input type=password name="new_password2" value="<?=$new_password2?>">
  </td>
 </tr>

 <?= errorRow("new_password2") ?>

 <tr>
  <td>
   Full Name
  </td>
  <td>
   <input type=text name="fullname" value="<?=$fullname?>">
  </td>
 </tr>

 <?= errorRow("fullname") ?>

 <tr>
  <td>
   Email Address
  </td>
  <td>
   <input type=text name="email" value="<?=$email?>">
  </td>
 </tr>

 <?= errorRow("email") ?>

<? if ($gallery->user->isAdmin() && isset($canCreate)) { ?>
 <tr>
  <td>
   User can create albums
  </td>
  <td> 
   <? if ($dontChange["create-albums"]) { ?>
	<?= $canCreateChoices[$canCreate] ?> <i>(admins can always create albums)</i>
   <? } else { ?>
        <?= drawSelect("canCreate", $canCreateChoices, $canCreate, 1); ?>
   <? } ?>
  </td>
 </tr>
<? } ?>

<? if ($gallery->user->isAdmin() && isset($isAdmin)) { ?>
 <tr>
  <td>
   User is an admin
  </td>
  <td> 
   <? if ($dontChange["admin"]) { ?>
	<?= $isAdminChoices[$isAdmin] ?> <i>(you can't un-admin yourself)</i>
   <? } else { ?>
        <?= drawSelect("isAdmin", $isAdminChoices, $isAdmin, 1); ?>
   <? } ?>
  </td>
 </tr>
<? } ?>

</table>
