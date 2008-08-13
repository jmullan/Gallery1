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

/**
 * @package setup
 */

/*
 * We assume setup/init.php was loaded !
 */

require_once(GALLERY_BASE . '/classes/User.php');
require_once(GALLERY_BASE . '/classes/gallery/User.php');
require_once(GALLERY_BASE . '/classes/NobodyUser.php');
require_once(GALLERY_BASE . '/classes/LoggedInUser.php');
require_once(GALLERY_BASE . '/classes/EverybodyUser.php');

$start_string = "# BEGIN Gallery section";
$end_string = "# END Gallery section.  Add User changes below this line";

$outfile = GALLERY_CONFDIR . '/config.php';
@copy("gpl.txt", $outfile);

if ($fd = @fs_fopen($outfile, "a")) {
	fwrite($fd, $gallery->session->configData);
	fclose($fd);
}
else {
	fail($outfile);
	return;
}


/**
 * Process the .htaccess file if present.
 */
if(fs_is_writable(GALLERY_CONFDIR . '/.htaccess')) {
	/* Read the .htaccess file */
	$htaccess = file(GALLERY_CONFDIR . '/.htaccess');
	$htaccess_save = array();
	$old_htaccess = true;
	$skipping = false;
	foreach ($htaccess as $line) {
		if ($skipping && trim($line) == $end_string) {
			$skipping = false;
		}
		else if ($skipping) {
			continue;
		}
		else if (trim($line) == $start_string) {
			$skipping = true;
			$old_htaccess = false;
		}
		else {
			$htaccess_save[]=$line;
		}
	}

	if ($old_htaccess) {
		$htaccess_save=array();
	}

	/*
	 * Begin the .htaccess file
	 */
	$outfile = GALLERY_CONFDIR . '/.htaccess';

	if ($fd = @fs_fopen($outfile, "w")) {
		fwrite($fd, "$start_string\n");
		fwrite($fd, "# (Automatically generated.  Do not edit this section)\n");
		fwrite($fd, "# Note: still under development, so format may change.\n");
		fwrite($fd, "# If you edit this file, make a backup before runnng the Config. Wizard.\n\n");
		fclose($fd);
	}

	/*
	 * Write the php_value lines, if they're permissable in the user's
	 * environment..
	 */
	if (isset($GALLERY_PHP_VALUE_OK)) {
		$htaccess = file("php_value.template");
		if ($fd = @fs_fopen($outfile, "a")) {
			foreach ($htaccess as $line) {
				if (!ereg('^#', $line)) {
					// Replace variables
					// Example: Literal $GALLERY_URL -> real $GALLERY_URL via preg_replace /e
					// modifier
					// Avoids use of eval
					// -Andy Lindeman (June 2008)
					$line = preg_replace('/\$\{?([A-Za-z][A-Za-z0-9_]*)\}?/e', '$${1}', $line);
				}
			}
			fclose($fd);
		}
	}

	/*
	 * Write the mod_rewrite lines, if that mod is available.
	 */
	if ($GALLERY_REWRITE_OK) {
		$htaccess = file("mod_rewrite.template");
		if ($fd = @fs_fopen($outfile, "a")) {
			foreach ($htaccess as $line) {
				// If the gallery is in the root directory, then we need to
				// make sure the .htaccess doesn't end up with "RewriteBase \/".
				if ($GALLERY_URL === '\\' && ereg('RewriteBase', $line)) {
					continue;
				}

				if (!ereg('^#', $line)) {
					// Replace variables
					// Example: Literal $GALLERY_URL -> real $GALLERY_URL via preg_replace /e
					// modifier
					// Avoids use of eval
					// -Andy Lindeman (June 2008)
					$line = preg_replace('/\$\{?([A-Za-z][A-Za-z0-9_]*)\}?/e', '$${1}', $line);
					fwrite($fd, $line);
				}
			}
			fclose($fd);
		}
	}

	/*
	 * End the .htaccess file gallery section
	 */
	if ($fd = @fs_fopen($outfile, "a")) {
		fwrite($fd, "$end_string\n");
		fclose($fd);
	}

	/*
	* Write the preserved data back to the .htaccess file
	*/
	if ($fd = @fs_fopen($outfile, "a")) {
		foreach ($htaccess_save as $line) {
			fwrite($fd, $line);
		}

		fclose($fd);
	}

}

/*
 * Don't require UserDB before we've written and included config.php or it will
 * have a cow when it tries to verify that the $gallery->app->userDir exists.
 */

include(GALLERY_BASE . '/config.php');
include_once(GALLERY_BASE . '/classes/UserDB.php');
include_once(GALLERY_BASE . '/classes/gallery/UserDB.php');

$userDB = new Gallery_UserDB();
$userDB->version = $gallery->user_version;
$admin = $userDB->getOrCreateUser("admin");

if (strlen($gallery->session->configForm->editPassword[2]) > 0) {
	$message = sprintf(gTranslate('config', "The password for user %s was changed."), '<b>admin</b>');
	$admin->setPassword($gallery->session->configForm->editPassword[2]);

	if (!$admin->isAdmin()) {
		$message = sprintf(gTranslate('config', "An account called %s has been created for you with the password you specified."),"<b>admin</b>");
		$admin->setIsAdmin(true);

		if (!$admin->getFullName()) {
			$admin->setFullName("Administrator");
		}

		if (!$admin->getEmail() && isset($adminEmail)) {
			$admin->setEmail($adminEmail);
		}
		$admin->setCanCreateAlbums(true);
	}

	$admin->save();
}
?>

<title><?php echo gTranslate('config', "Success!") ?></title>
<center>
<div style="width:60%; text-align:center">
	<div class="header"><?php echo gTranslate('config', "Your configuration has been successfully saved!") ?></div>

<?php
if (getOS() != OS_WINDOWS) {
	if (!empty($message)) {
		$message .= "<br><br>";
	}
	else {
		$message = gTranslate('config', "We highly suggest that you run 'secure.sh', or chmod your .htaccess and config.php to 644 for increased security.");
	}
}

// Inform the user that their gallery is not secure until they remove the 'resetadmin' file
if (fs_file_exists(GALLERY_SETUPDIR . "/resetadmin")) {
	if (!empty($message)) $message .= "<br><br>";
	$message .= '<span class="attention">'
	. gTranslate('config', "Note")
	. ':<br>'
	. sprintf(gTranslate('config', "You must remove the '%s' file from the setup directory to make your Gallery configuration secure"), "resetadmin")
	. '</span>' ;
}

if (isset($message)) {
	echo '<p class="sitedesc">';
	echo "\n\t$message";
	echo "\n</p>";
}
?>
</div>

<p style="width:80%; text-align:center">
<?php
echo gTranslate('config', "You can reconfigure Gallery at any time by re-running this configuration wizard.");
echo '<br>';
echo gTranslate('config', "It will save your data so you won't have to enter it all again!");
echo '<br>';
echo '<span class="emphasis">';
printf(gTranslate('config', "Please subscribe to the %s mailing list. This is a very low volume mailing list that will keep you abreast of important changes in %s.  This is where we announce new versions, important bug fixes and any security issues and their resolution."),
	'<a href="http://gallery.sourceforge.net/lists.php" class="namedurl">gallery-announce</a>',
Gallery());
?>
	</span>
	<p>

	<font size="+2"><a href="../albums.php"><?php echo gTranslate('config', "Enter the Gallery") ?></a></font>

	<p><img src="../images/gallery-tag.png"></p>
</div>
</body>
</html>

<?php
// Clear the session config data
unset($gallery->session->configForm);
unset($gallery->session->configData);

function fail($file) {
?>
	<title><?php echo gTranslate('config', "Failure!") ?>!</title>
	<font size="+2"><?php printf(gTranslate('config', "Unable to write to %s."), $file )?></font>
	<p>
	<font color="red">
	<?php printf (gTranslate('config', "You are missing either %s or %s."), "<i>config.php</i>", "<i>.htaccess</i>") ?>
	</font>
	<p>
	<input type="hidden" name="back_page" value="confirm">
	<input type="submit" name="go_back" disabled="disabled" value="<- <?php echo gTranslate('config', "Review Settings") ?>">

<?php
return;
}
?>
