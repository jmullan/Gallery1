<?php /* $Id$ */ ?>

<?php

require_once("../classes/User.php");
require_once("../classes/gallery/User.php");
require_once("../classes/NobodyUser.php");
require_once("../classes/LoggedInUser.php");
require_once("../classes/EverybodyUser.php");

require("../errors/configure_instructions.php");
$start_string = "# BEGIN Gallery section";
$end_string = "# END Gallery section.  Add User changes below this line";

$outfile = "$GALLERY_DIR/config.php";
@copy("gpl.txt", $outfile);

if ($fd = @fs_fopen($outfile, "a")) {
	$data = urldecode($data);

	fwrite($fd, $data);
	fclose($fd);
} else {
	fail($outfile);
	return;
}


/*
 * Read the .htaccess file
 */
$htaccess = file("$GALLERY_DIR/.htaccess");
$htaccess_save = array();
$old_htaccess = true;
$skipping = false;
foreach ($htaccess as $line) {
	if ($skipping && trim($line) == $end_string) {
		$skipping = false;
	} else if ($skipping) {
		continue;
	} else if (trim($line) == $start_string) {
		$skipping = true;
		$old_htaccess = false;
	} else {
		$htaccess_save[]=$line;
	}
}
if ($old_htaccess) {
	$htaccess_save=array();
}
/*
 * Begin the .htaccess file
 */
$outfile = "$GALLERY_DIR/.htaccess";
if ($fd = @fs_fopen($outfile, "w")) {
       	fwrite($fd, "$start_string\n");
       	fwrite($fd, "# (Automatically generated.  Do no edit this section)\n");
       	fwrite($fd, "# Note: still under development, so format may change.\n");
       	fwrite($fd, "# If you edit this file, make a backup before runnng the Config. Wizard.\n\n");
       	fclose($fd);
} else {
       	fail($outfile);	
	return;
}

/*
 * Write the php_value lines, if they're permissable in the user's
 * environment..
 */
if (isset($GALLERY_PHP_VALUE_OK)) {
	$htaccess = file("php_value.template");
	if ($fd = @fs_fopen($outfile, "a")) {
		foreach ($htaccess as $line) {
			eval("\$line = \"$line\";");
			fwrite($fd, $line);
		}
		fclose($fd);
	} else {
		fail($outfile);	
		return;
	}
}

/*
 * Write the mod_rewrite lines, if that mod is available.
 */
if ($GALLERY_REWRITE_OK) {
	$htaccess = file("mod_rewrite.template");
	if ($fd = @fs_fopen($outfile, "a")) {
		foreach ($htaccess as $line) {
			eval("\$line = \"$line\";");
			fwrite($fd, $line);
		}
		fclose($fd);
	} else {
		fail($outfile);	
		return;
	}
}

/*
 * End the .htaccess file gallery section
 */
$outfile = "$GALLERY_DIR/.htaccess";
if ($fd = @fs_fopen($outfile, "a")) {
       	fwrite($fd, "$end_string\n");
       	fclose($fd);
} else {
       	fail($outfile);	
	return;
}

/*
 * Write the preserved data back to the .htaccess file 
 */
$outfile = "$GALLERY_DIR/.htaccess";
if ($fd = @fs_fopen($outfile, "a")) {
       	foreach ($htaccess_save as $line) {
	       	fwrite($fd, $line);
       	}
       	fclose($fd);
} else {
       	fail($outfile);	
	return;
}
/*
 * Don't require UserDB before we've written and included config.php or it will
 * have a cow when it tries to verify that the $gallery->app->userDir exists.
 */
include("../config.php");
include_once("../classes/UserDB.php");
include_once("../classes/gallery/UserDB.php");

$userDB = new Gallery_UserDB();
$admin = $userDB->getOrCreateUser("admin");

if (strlen($editPassword) >0) {
	$message=sprintf(_("The Password for user %s was changed"),"'admin'");
	$admin->setPassword($editPassword);
	
	if (! $admin->isAdmin()) {
		$message=sprintf(_("An account called %s has been created for you with the password you specified."),"'admin'");
		$admin->setIsAdmin(true);
		if (!$admin->getFullName()) {
			$admin->setFullName("Administrator");
		}
		if (!$admin->getEmail() && isset($adminEmail)) {
       			$admin->setEmail($adminEmail);
		}
		$admin->setCanCreateAlbums(true);
		$admin->save();
	}
}
?>

<title><?php echo _("Success!") ?></title>
<center>
<div style="width:600px;text-align:center">

<div class="header"><?php echo _("Your configuration has been successfully saved!") ?></div>
<?php
if (isset($message)) {
	echo '<p><table cellspacing="1" cellpadding="4" border="1">';
	echo "\n\t<tr><td>$message</td></tr>";
	echo "\n</table></p>";
}
?>
<div class="sitedesc"><span style="font-size: 1.2em; color:red"><?php echo _("Note") ?>!</span>

<br>
<?php 
	echo _("Don't forget to run this to make your Gallery secure!");
	configure("secure");
?>
</div>
<br>
<font size=+1>
<?php echo _("Gallery won't run if it is not in secure mode") ?></font>.
<?php echo _("You can reconfigure Gallery at any time by re-running this configuration wizard.") ?>  
<?php echo _("It will save your data so you won't have to enter it all again!") ?>

<p>
<center>
<font size=+2>
<a href="../albums.php"><?php echo _("Enter the Gallery") ?></a>
</font>

<br><br><br><br>

<img src="../images/gallery-tag.png">

</div>

<?php
function fail($file) {
?>
	<title><?php echo _("Failure") ?>! </title>
	<font size=+2><?php printf(_("Unable to write to %s."), $file )?> </font>
	<p>
<font color=red>
	<?php printf (_("You are missing either %s or %s."), "<i>config.php</i>", "<i>.htaccess</i>") ?>
	</font>
	<p>
	<input type=hidden name=back_page value="confirm">
	<input type=submit name="go_back" value="<- <?php echo _("Review Settings") ?>">

<?php
	return;
}
?>
