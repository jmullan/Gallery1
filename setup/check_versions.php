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
if (!function_exists('fs_is_readable')) {
       	function fs_is_readable($filename) {
	       	return @is_readable($filename);
       	}
}

// No translation yet, as we may not release this in 1.4.1
function checkVersions() {
	global $GALLERY_BASEDIR, $gallery;
	$manifest=$GALLERY_BASEDIR."setup/manifest.inc";
	$errors=array();
	if (!fs_file_exists($manifest)) {
	       	$errors[$manifest]="File missing or unreadable.  Please install then re-run this test.";
		return $errors;
	}
	if (!function_exists('getCVSVersion')) {
		$errors['util.php']="Please ensure that util.php is the latest version.";
		return $errors;
	}
	include $manifest;
	foreach ($versions as $file => $version) {
		$found_version=getCVSVersion($file);
		if ($found_version === NULL) {
		       	if (isDebugging()) {
			       	print sprintf("Cannot read file %s.", $file);
			       	print "<br>\n";
			}
			$errors[$file]="File missing or unreadable.";
			continue;
		} else if ($found_version === "") {
		       	if (isDebugging()) {
			       	print sprintf("Version information not found in %s.  File must be old version or corrupted.", $file);
			       	print "<br>\n";
		       	}
		       	$errors[$file]="Missing version";
		       	continue;
	       	} else if ($found_version < $version) {
		       	if (isDebugging()) {
			       	print sprintf("Problem with %s.  Expected version %s (or greater) but found %s.", $file, $version, $found_version);
			       	print "<br>\n";
		       	}
		       	$errors[$file]=sprintf("Expected version %s (or greater) but found %s.", $version, $found_version);
	       	} else if ($found_version > $version) {
		       	if (isDebugging()) {
				print sprintf("%s OK.  Actual version (%s) more recent than expected version (%s)", $file, $found_version, $version);
			       	print "<br>\n";
			}
		} else {
		       	if (isDebugging()) {
			       	print sprintf("%s OK", $file);
			       	print "<br>\n";
		       	}
		}
			
	}
	return $errors;
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
