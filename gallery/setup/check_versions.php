<?php /* $Id$ */ ?>
<?php 
	$GALLERY_BASEDIR="../";
	require($GALLERY_BASEDIR . "setup/init.php");
	require($GALLERY_BASEDIR . 'setup/functions.inc');
?>
<?php echo doctype(); ?>
<html>
<head>
  <title> <?php echo _("Check Versions") ?> </title>
  <?php common_header(); ?>
  <style>
	.shortdesc { width:30% }
  </style>  
</head>

<body dir="<?php echo $gallery->direction ?>">
<h1 class="header"><?php echo _("Check Versions") ?></h1>
<div class="sitedesc"><?php
	echo sprintf(_("This page gives you information about the version of each necessary %s file"),"Gallery");
	echo _("If you see error, we highly suggest to get the actual version of that file/s");
?></div>

<table class="inner" width="100%">
<tr>
	<td class="desc"><?php 
if (empty($show_details)) {
       	$show_details=0;
}
if ($show_details) {
       	print sprintf(_("%sClick here%s to hide the details"),
		       	'<a href="check_versions.php?show_details=0">','</a>');
} else {
       	print sprintf(_("%sClick here%s to see more details"),
		       	'<a href="check_versions.php?show_details=1">','</a>');
}
?></td>
</tr>
</table>             

<?php

list($oks, $errors, $warnings)=checkVersions(false);
if  ($errors) { ?>
<table class="inner" width="100%">
<tr>
	<td class="errorlong" colspan="2"><?php print sprintf(_("%s missing, corrupt or older than expected."), 
						pluralize_n(count($errors), _("1 file"), 
						_("files"), _("No files"))); ?></td>
</tr>
<?php 
	if ($show_details) { ?>
<tr>
	<td class="desc" colspan="2"><?php print sprintf(_("There are problems with the following files.  Please correct them before configuring %s."), Gallery()); ?></td>
</tr><?php
		foreach ($errors as $file => $error) {
			echo "\n<tr>";
			echo "\n\t<td class=\"shortdesc\">$file:</td>";
			echo "\n\t<td class=\"desc\">$error</td>";
			echo "\n</tr>";
	       	}
	}
}
?>

</table>

<table class="inner" width="100%">
<tr>
<?php
if ($warnings) { ?>
	<td class="warninglong" colspan="2"><?php print sprintf(_("%s more recent than expected."), 
							pluralize_n(count($warnings), _("1 file"), _("files"), _("No files"))); ?></td>
</tr>
<?php
	if ($show_details) {?>
<tr>
	<td class="desc" colspan="2"><?php 
		echo sprintf(_("The following files are more up-to-date than expected for this version of %s.  If you are using pre-release code, this is OK."), Gallery());
		echo "</td>";
		echo "\n</tr>";
		foreach ($warnings as $file => $warning) {
			echo "\n<tr>";
			echo "\n\t<td class=\"shortdesc\">$file:</td>";
			echo "\n\t<td class=\"desc\">$warning</td>";
			echo "\n</tr>";
		}
	}
}
?>

</table>

<table class="inner" width="100%">
<tr>
	<td class="successlong" colspan="2"><?php print sprintf(_("%s up-to-date."), 
						pluralize_n(count($oks), _("1 file"), 
						_("files"), _("No files"))); ?></td>
</tr><?php 
if ($show_details && $oks) {
	echo "\n<tr>";
	echo "\n\t<td class=\"desc\" colspan=\"2\">" . _("The following files are up-to-date.") . "</td>";
	echo "\n</tr>";		
	foreach ($oks as $file => $ok) {
		echo "\n<tr>";
		echo "\n\t<td class=\"shortdesc\">$file:</td>";
		echo "\n\t<td class=\"desc\">$ok</td>";
		echo "\n</tr>";
	}
}
?>

</table>

<p align="center"><?php echo returnToConfig(); ?></p>

</body>
</html>
