<?php /* $Id$ */ ?>
<?php 
	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . "setup/init.php");
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
print "<p>";          

list($oks, $errors, $warnings)=checkVersions(false);
if  ($errors) { ?>
	<p>
	<span class="errorlong">
       	<?php print sprintf(_("%s missing, corrupt or older than expected."), 
			pluralize_n(count($errors), _("1 file"), 
				_("files"), _("No files"))); ?>
	</span>
	<?php if ($show_details) { ?>
		<p>
	       	<?php print sprintf(_("There are problems with the following files.  Please correct them before configuring %s."), Gallery()); ?>
	       	<br>
		<?php
	       	foreach ($errors as $file => $error) {
		       	print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$error<br>\n";
	       	}
       	}
}
if  ($warnings) { ?>
	<p>
	<span class="warninglong">
       	<?php print sprintf(_("%s more recent than expected."), 
			pluralize_n(count($warnings), _("1 file"), 
				_("files"), _("No files"))); ?>
	       	</span>
	       	<?php if ($show_details) {?>
		       	<br><br>
			<?php 
			print sprintf(_("The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is OK."), Gallery());
			foreach ($warnings as $file => $warning) {
			       	print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$warning<br>\n";
		       	}
	       	}
}
?>

<p>

<span class="successlong">
<?php print sprintf(_("%s up-to-date."), 
		pluralize_n(count($oks), _("1 file"), 
			_("files"), _("No files"))); ?>
</span>
<?php if ($show_details && $oks) { ?>
	       	<br><br>
	       	<?php 
		print _("The following files are up-to-date.");
	       	foreach ($oks as $file => $ok) {
	       		print "<div class=\"emphasis\">$file:</div> &nbsp;&nbsp;&nbsp;&nbsp;$ok<br>\n";
	       	}
}
?>
</body>
</html>
