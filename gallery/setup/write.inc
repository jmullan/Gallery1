<!-- $Id$ -->
<?php
require("../classes/User.php");
require("../classes/gallery/User.php");
require("../classes/NobodyUser.php");
require("../classes/LoggedInUser.php");
require("../classes/EverybodyUser.php");
require("../errors/configure_instructions.php");

$outfile = "$GALLERY_DIR/config.php";
copy("gpl.txt", $outfile);

if ($fd = @fs_fopen($outfile, "a")) {
	$data = urldecode($data);

	fwrite($fd, $data);
	fclose($fd);
} else {
	fail($outfile);
	return;
}

/*
 * Empty out the .htaccess file
 */
$outfile = "$GALLERY_DIR/.htaccess";
if ($fd = @fs_fopen($outfile, "w")) {
	fclose($fd);
}

/*
 * Write the php_value lines, if they're permissable in the user's
 * environment..
 */
if ($GALLERY_PHP_VALUE_OK) {
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
 * Don't require UserDB before we've written and included config.php or it will
 * have a cow when it tries to verify that the $gallery->app->userDir exists.
 */
include("../config.php");
include("../classes/UserDB.php");
include("../classes/gallery/UserDB.php");

$userDB = new Gallery_UserDB();
$admin = $userDB->getOrCreateUser("admin");
$admin->setPassword($editPassword);
$admin->setIsAdmin(true);
$admin->setFullName("Administrator");
$admin->setCanCreateAlbums(true);
$admin->save();

?>

<title> Success! </title>
<center>
<table width=600><tr><td>
<center>
<font size=+2> Your configuration has been successfully saved! </font>
<p>
<table cellspacing="1" cellpadding="4" border="1"><tr><td>
An account called <i>admin</i> has been created for you with the password you specified.
</td></tr></table>
<p>
<font size=+2 color=red>Note!</font>
<br>
Don't forget to run:
<?php echo configure("secure") ?>
</center>
<br>
to make your Gallery secure!  <font size=+1>Gallery won't run if it is not in secure
mode</font>.  You can reconfigure Gallery at any time by re-running this
configuration wizard (it will save your data so you won't have to enter
it all again!)

<p>
<center>
<font size=+2>
<a href=../albums.php>Enter the Gallery</a>
</font>

<br><br><br><br>

<img src="../images/gallery-tag.png">
</center>

</td></tr></table>



<?php
function fail($file) {
?>
	<title> Failure! </title>
	<font size=+2> Unable to write to <?php echo $file?> </font>
<?php
	return;
}
?>
