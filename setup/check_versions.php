<?php /* $Id$ */ ?>
<?php 
	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . "setup/init.php");

function checkVersions() {
	global $GALLERY_BASEDIR, $gallery, $show_details;
	$manifest=$GALLERY_BASEDIR."manifest.inc";
	$errors=array();
	$warnings=array();
	$oks=array();
	if (!fs_file_exists($manifest)) {
	       	$errors["manifest.inc"]=_("File missing or unreadable.  Please install then re-run this test.");
		return array($errors, $warnings, $oks);
	}
	if (!function_exists('getCVSVersion')) {
		$errors['util.php']=sprintf(_("Please ensure that %s is the latest version."), "util.php");
		return array($errors, $warnings, $oks);
	}
	include $manifest;
	print sprintf(_("Testing status of %d files."), count($versions));
	foreach ($versions as $file => $version) {
		$found_version=getCVSVersion($file);
		if ($found_version === NULL) {
		       	if (!empty($show_details)) {
			       	print "<br>\n";
			       	print sprintf(_("Cannot read file %s."), $file);
			}
			$errors[$file]=_("File missing or unreadable.");
			continue;
		} else if ($found_version === "") {
		       	if (!empty($show_details)) {
			       	print "<br>\n";
			       	print sprintf(_("Version information not found in %s.  File must be old version or corrupted."), $file);
		       	}
		       	$errors[$file]=_("Missing version");
		       	continue;
	       	} else if ($found_version < $version) {
		       	if (!empty($show_details)) {
			       	print "<br>\n";
			       	print sprintf(_("Problem with %s.  Expected version %s (or greater) but found %s."), $file, $version, $found_version);
		       	}
		       	$errors[$file]=sprintf(_("Expected version %s (or greater) but found %s."), $version, $found_version);
	       	} else if ($found_version > $version) {
		       	if (!empty($show_details)) {
			       	print "<br>\n";
				print sprintf(_("%s OK.  Actual version (%s) more recent than expected version (%s)"), $file, $found_version, $version);
			}
			$warnings[$file]=sprintf(_("%s is a more recent version than expected.  Expected version %s but found %s."), $file, $version, $found_version);
		} else {
		       	if (!empty($show_details)) {
			       	print "<br>\n";
			       	print sprintf(_("%s OK"), $file);
		       	}
			$oks[$file]="OK";
		}
			
	}
       	return array($errors, $warnings, $oks);
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
if (!empty($show_details)) {
       	print sprintf(_("%sClick here%s to hide the details"),
		       	'<a href="check_versions.php?show_details=0">','</a>');
} else {
       	print sprintf(_("%sClick here%s to see more details"),
		       	'<a href="check_versions.php?show_details=1">','</a>');
}
print "<p>";

$results=checkVersions();
$errors=$results[0];
$warnings=$results[1];
$oks=$results[2];
if  ($errors) {
	print "<p>";
	print '<span class="errorlong">';
       	print sprintf(_("The following files are missing or not the correct version for this version of %s.  Please replace them with the correct version."), Gallery());
	print '</span>';
       	print "<br><br>\n";
       	foreach ($errors as $file => $error) {
	       	print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$error<br>\n";
       	}
}
if  ($warnings) {
	print "<p>";
	print '<span class="warninglong">';
       	print sprintf(_("The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is expected."), Gallery());
	print '</span>';
       	print "<br><br>\n";
       	foreach ($warnings as $file => $warning) {
	       	print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$warning<br>\n";
       	}
} ?>
<br><br><span class="successlong">
<?php print sprintf(_("%d files up-to-date."), count($oks)); ?>
<br>
</span>

</body>
</html>
