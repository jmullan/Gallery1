<?
require("../classes/User.php");
require("../classes/gallery/User.php");
require("../classes/NobodyUser.php");
require("../classes/EverybodyUser.php");

$outfile = "$GALLERY_DIR/config.php";
copy("gpl.txt", $outfile);

if ($fd = @fopen($outfile, "a")) {
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
<table width=450><tr><td>
<center>
<font size=+2> Your configuration has been successfully saved! </font>
</center>
<p>
An account called <i>admin</i> has been created for you with the password you specified.
<p>
<center>
<font size=+2 color=red>Note!</font>
<br>
Don't forget to run:
<br>
<code>
<br> <b>Unix</b>
<br> % sh secure.sh
<br>
<br> <b>Windows</b>
<br> C:\> secure.bat
</code>
</center>
<br>
in your Gallery directory to make your application secure!  Gallery
won't run if it is not in secure mode.  You can reconfigure Gallery
at any time by re-running <code>configure.sh</code> and then returning
to the setup URL.

<p>
<center>
<font size=+2>
<a href=../albums.php>Enter the Gallery</a>
</font>

<br><br><br><br>

<font size=+1>
Help Gallery become a better product!
<br>
Fill out the <a href=http://gallery.sourceforge.net?action=survey>Gallery Survey</a>.
</font>

<br><br>

<img src="../images/gallery-tag.png">
</center>

</td></tr></table>



<?
function fail($file) {
?>
	<title> Failure! </title>
	<font size=+2> Unable to write to <?=$file?> </font>
<?
	return;
}
?>
