<?php /* $Id$ */ ?>
<?php 
	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . "setup/init.php");


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
if (empty($show_details)) {
       	$show_details=false;
}
if ($show_details) {
       	print sprintf(_("%sClick here%s to hide the details"),
		       	'<a href="check_versions.php?show_details=0">','</a>');
} else {
       	print sprintf(_("%sClick here%s to see more details"),
		       	'<a href="check_versions.php?show_details=1">','</a>');
}             
print "<p>";          

list($oks, $errors, $warnings)=checkVersions($show_details);
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
       	print sprintf(_("The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is OK."), Gallery());
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
