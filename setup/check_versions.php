<?php /* $Id$ */ ?>
<?php 

	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . "util.php");
	require($GALLERY_BASEDIR . "setup/init.php");

	initLanguage();
	if (getOS() == OS_WINDOWS) {
		include($GALLERY_BASEDIR . "platform/fs_win32.php");
		if (fs_file_exists("SECURE")) {
			print _("You cannot access this file while gallery is in secure mode.");
			exit;
		}
	}

	// We set this to false to get the config stylesheet
        $GALLERY_OK=false;
	extract($HTTP_POST_VARS);
	require($GALLERY_BASEDIR . "setup/functions.inc");
?>

<html>
<head>
	<title> <?php echo _("Check Versions") ?> </title>
	<?php echo getStyleSheetLink() ?>
</head>

<body dir="<?php echo $gallery->direction ?>">
<h1 class="header"><?php echo _("Check Versions") ?></h1>

<?php 
// $gallery->app->debug = "yes";

$errors=checkVersions();
if  ($errors) {
	print '<span class="errorlong">';
       	print sprintf("The following files are missing or not the correct version for this version of %s.  Please replace them with the correct version.", Gallery());
	print '</span>';
       	print "<br><br>\n";
       	foreach ($errors as $file => $error) {
	       	print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$error<br>\n";
       	}
} else {
	print '<br><br><span class="Success">';
       	print "All versions up-to-date<br>";
	print '</span>';
}

?>
</body>
</html>
