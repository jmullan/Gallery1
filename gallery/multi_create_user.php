<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * This additional file was created by Joan McGalliard 
 *	http://www.mcgalliard.org
 *
 * $Id$
 */
?>
<?php

require(dirname(__FILE__) . '/init.php');

if (!$gallery->user->isAdmin() || $gallery->app->multiple_create != "yes") {
	echo _("You are no allowed to perform this action !");
	exit;	
}

$errorCount=0;
if (isset($create))
{
	doctype();
	?>
<html>
<head>
  <title><?php echo _("Create Multiple Users") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<?php
	if (empty($membersfile_name)) {
		$gErrors["membersfile"] = _("No file selected.");
		$errorCount++;
	} else {
	       	if (!is_uploaded_file($membersfile)) {
		       	$gErrors["membersfile"] = 
				sprintf(_("Upload failed: %s."), $membersfile_name);
		       	$errorCount++;
	       	}
	}
	if (!$errorCount) {
		$users=file($membersfile);
		// Simple test to see if it's a windows file
		if (sizeof($users)==1 and ereg("", $users[0]))
		{
			$users=explode("", $users[0]);
		}
		unlink($membersfile);
		$total_added=0;
		$total_skipped=0;
		foreach ($users as $user)
		{
			set_time_limit($gallery->app->timeLimit);
			$user=trim($user);
			$uname=strtok($user, ' 	');
			if ($uname=="")
				continue;
			$email=strtok(' 	');
			$fullname=trim(strtok(''));
			processingMsg("- ". sprintf (_("adding %s"),$uname));
			$password=generate_password(10);
			$tmpUser= $gallery->userDB->CreateUser($uname, $email, $password, $fullname,
				$canCreate, $defaultLanguage, "bulk_register");
			if ($tmpUser) {
				$total_added++;
				if ($send_email=="on") {
				       	processingMsg("- " . sprintf(_("Send email to %s"),$email));
				       	$msg = ereg_replace("!!PASSWORD!!", $password,
						       	ereg_replace("!!USERNAME!!", $uname,
							       	ereg_replace("!!FULLNAME!!", $fullname,
								       	ereg_replace("!!NEWPASSWORDLINK!!", 
										$tmpUser->genRecoverPasswordHash(),
									       	welcome_email()))));
				       	$logmsg = sprintf(_("%s has registered by %s.  Email has been sent to %s."),
						       	$uname, $gallery->user->getUsername(), $email);
				       	$logmsg2  = sprintf("%s has registered by %s.  Email has been sent to %s.",
						       	$uname, $gallery->user->getUsername(), $email);
				       	if ($logmsg != $logmsg2) {
					       	$logmsg .= " <<<<>>>>> $logmsg2";
				       	}

					if (!gallery_mail($email, _("Gallery Registration"),$msg, $logmsg)) {
						processingMsg(sprintf(_("Problem with email to %s"), $uname));
						print "<br>";
				       	} else {
					       	clearstatcache();
					       	$tmpUser->save();
					}
			       	}
		       	} else {
			       	$total_skipped++;
		       	}
		}
	       	print "<br><br>" .
		       	sprintf(_("%s added, %s skipped"), 
				pluralize_n2(ngettext("1 user", "%d users", $total_added), $total_added),
			       	pluralize_n2(ngettext("1 user", "%d users", $total_skipped), $total_skipped));
	       	?>
		       	<center><br><br>
		       	<form><input type="submit" name="dismiss" value="<?php echo _("Dismiss") ?>"></form>
		       	<?php
		       	exit;
	}	

} else if (isset($cancel) || isset($dismiss)) {
	header("Location: " . makeGalleryHeaderUrl("manage_users.php"));
} else {
	doctype();
}
?>

<html>
<head>
  <title><?php echo _("Create Multiple Users") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">


<center>
<span class="popuphead"><?php echo _("Create Users") ?></span>
<br>
<br>
<?php echo _("Create multiple new users from a file.") ?>
<p>
<?php
$allowChange["uname"] = false;
$allowChange["email"] = false;
$allowChange["password"] = false;
$allowChange["old_password"] = false;
$allowChange["fullname"] = false;
$allowChange["send_email"] = true;
$allowChange["create_albums"] = true;
$allowChange["default_language"] = true;
$allowChange["member_file"] = true;

echo makeFormIntro("multi_create_user.php", array(
		       	"name" => "usercreate_form", 
			"enctype" => "multipart/form-data",
			"method" => "POST",));
	$canCreateChoices = array(1 => _("yes"), 0 => _("no"));
	$canCreate = 0;
?>
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000">

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<input type="submit" name="create" value="<?php echo _("Create") ?>">
<input type="submit" name="cancel" value="<?php echo _("Cancel") ?>">
</form>

</center>
<b><?php echo _("Notes:") ?> </b>
<ul>
<li>
<?php echo _("The members file should be one user per line, and the fields should be space separated.  Each line is of the form:<br> <i>username emailaddress fullname</i>.<br>  Only username is required. Everything after the email address is the full name, so there can be spaces in it.<p>") ?>


<li>
<?php 
echo _("The strings !!USERNAME!!, !!FULLNAME!! and !!PASSWORD!! will be substituted in the email with the values from the membership file.  An individual email will be sent to each member with a valid email address in the members file (if &quot;send emails&quot; checkbox is ticked).") 
?>
<p>

</ul>

</body>
</html>
