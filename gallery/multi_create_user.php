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
 * This additional file was created by Joan McGalliard
 *	http://www.mcgalliard.org
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

list($formaction, $defaultLanguage, $canCreate, $canChangeOwnPw, $isAdmin, $send_email, $dismiss) =
getRequestVar(array('formaction', 'defaultLanguage', 'canCreate', 'canChangeOwnPw', 'isAdmin', 'send_email', 'dismiss'));

if (!$gallery->user->isAdmin() || $gallery->app->multiple_create != "yes") {
    echo _("You are not allowed to perform this action!");
    exit;
}

$errorCount = 0;
if ($formaction == 'create') {
    doctype();
    ?>
<html>
<head>
  <title><?php echo _("Create Multiple Users") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>" class="popupbody">
<div class="popuphead"><?php echo _("Create Multiple Users") ?></div>
<div class="popup">
<?php
    if (empty($_FILES['membersfile']['name'])) {
        $gErrors["membersfile"] = _("No file selected.");
        $errorCount++;
    } else {
        if (!is_uploaded_file($_FILES['membersfile']['tmp_name'])) {
            $gErrors["membersfile"] =
            sprintf(_("Upload failed: %s."), $_FILES['membersfile']['name']);
            $errorCount++;
        }
    }

    if (!$errorCount) {
        $handle = fopen ($_FILES['membersfile']['tmp_name'],"r");
        while ( ($user= fgetcsv ($handle, 1000, " ")) !== FALSE ) {
            $users[] = $user;
        }
       fclose ($handle);
    }
    if (sizeof($users) == 0) {
        $gErrors["membersfile"] =
        sprintf(_("Upload went fine, but the file is not readable, please make sure %s is accessable for your webserver. (Also check openbasedir restrictions.)"),
        dirname($_FILES['membersfile']));
        $errorCount++;
    }

    if (!$errorCount) {
        // Simple test to see if it's a windows file
        if (sizeof($users) == 1 and ereg("\r\n", $users[0])) {
            $users = explode("\r\n", $users[0]);
        }
        unlink($_FILES['membersfile']['tmp_name']);
        $total_added = 0;
        $total_skipped = 0;
        foreach ($users as $user) {
            set_time_limit($gallery->app->timeLimit);
            $uname = $user[0];
            
            if (sizeof($user) == 2) {
                if(check_email($user[1])) {
                    $email = $user[1];
                    $fullname = NULL;
                } else {
                    $email = NULL;
                    $fullname = $user[1];
                }
            } else {
                $email = $user[1];
                $fullname = $user[2];
            }
            if ($email) {
                processingMsg("- ". sprintf (_("Adding %s (%s) with email: %s"),
                    $uname, (!empty($fullname) ? $fullname : '<i>' . _("No fullname given") .'</i>'), $email));
            }
            else {
                processingMsg("- ". sprintf (_("Adding %s (%s)"),
                    $uname, (!empty($fullname) ? $fullname : '<i>' . _("No fullname given") .'</i>')));
            } 
            $password = generate_password(10);
            $tmpUser = $gallery->userDB->CreateUser($uname, $email, $password, $fullname, $canCreate, $defaultLanguage, "bulk_register");
            if ($tmpUser) {
                $total_added++;
                if ($send_email && !empty($email)) {
                    processingMsg("- " . sprintf(_("Send email to %s"),$email));
                    $msg = ereg_replace("!!PASSWORD!!", $password,
                        ereg_replace("!!USERNAME!!", $uname,
                        ereg_replace("!!FULLNAME!!", $fullname,
                        ereg_replace("!!NEWPASSWORDLINK!!",
                        $tmpUser->genRecoverPasswordHash(),
                        welcome_email()))));
                    $logmsg = sprintf(_("New user '%s' has been registered by %s.  Gallery has sent a notification email to %s."),
                        $uname, $gallery->user->getUsername(), $email);
                    $logmsg2  = sprintf("New user '%s' has been registered by %s.  Gallery has sent a notification email to %s.",
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
        echo "\n<p>";
        echo sprintf(_("%s added, %s skipped"),
	    gTranslate('core', "1 user", "%d users", $total_added),
	    gTranslate('core', "1 user", "%d users", $total_skipped));
        echo "\n</p>";      
?>

<center>
    <form><input type="submit" name="dismiss" value="<?php echo _("Back to usermanagement") ?>"></form>
</center>
</div>
</body>
</html>
<?php
    exit;
    }
} else if ($formaction == 'cancel' || isset($dismiss)) {
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
<div class="popuphead"><?php echo _("Create Users") ?></div>
<div class="popup" align="center">
<?php echo _("Create multiple new users from a file.") ?>
<p>
<?php
$allowChange["uname"] = false;
$allowChange["email"] = false;
$allowChange["password"] = false;
$allowChange["old_password"] = false;
$allowChange["fullname"] = false;
$allowChange["send_email"] = true;
$allowChange["default_language"] = true;
$allowChange["member_file"] = true;
$allowChange["create_albums"] = true;
$allowChange["canChangeOwnPw"] = true;
$allowChange["admin"] = true;

echo makeFormIntro("multi_create_user.php", array(
    "name" => "usercreate_form",
    "enctype" => "multipart/form-data"));

$canCreateChoices = array(1 => _("Yes"), 0 => _("No"));
$canCreate = 0;
?>
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000">

<?php include(dirname(__FILE__) . '/html/userData.inc'); ?>

<br>
	<input type="hidden" name="formaction" value="">
    <input type="submit" name="create" value="<?php echo _("Create") ?>" onclick="usercreate_form.formaction.value='create'">   
    <input type="submit" name="cancel" value="<?php echo _("Back to usermanagement") ?>" onclick="usercreate_form.formaction.value='cancel'">
</form>

</div>
<div class="popup">
<b><?php echo _("Notes:") ?> </b>
<ul>
<li>
<?php echo _("The members file should be one user per line, and the fields should be space separated."); ?>
<br><?php echo _("Each line must be in one of this formats:"); ?>
    <ul>
    <li><?php echo _("<i>username emailaddress fullname</i>"); ?></li>
    <li><?php echo _("<i>username fullname</i>"); ?></li>
    <li><?php echo _("<i>username emailaddress</i>"); ?></li>
    </ul>
<?php echo _("Only username is required. Everything after the email address is the full name, so there can be spaces in it.") ?>
</li>
<br>
<li>
<?php echo _("The strings !!USERNAME!!, !!FULLNAME!! and !!PASSWORD!! will be substituted in the email with the values from the membership file.  An individual email will be sent to each member with a valid email address in the members file (if &quot;send emails&quot; checkbox is ticked).") ?>
</li>
</ul>
</div>
</body>
</html>
