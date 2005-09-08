<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2005 Bharat Mediratta
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
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */
?>
<?php 
    require_once(dirname(__FILE__) . '/init.php');

    $show_details = getRequestVar('show_details');

    echo doctype();
?>
<html>
<head>
  <title> <?php echo _("Check Versions") ?> </title>
  <?php common_header(); ?>
  <style>
	.shortdesc { width:30% }
  </style>  
</head>

<body dir="<?php echo $gallery->direction ?>">
<?php configLogin(basename(__FILE__)); ?>
<h1 class="header"><?php echo _("Check Versions") ?></h1>
<div class="sitedesc"><?php
	echo sprintf(_("This page gives you information about the version of each necessary %s file. "),"Gallery");
	echo _("If you see any error(s), we highly suggest to get the actual version of that file(s).");
?></div>

<table class="inner" width="100%">
<tr>
	<td class="desc"><?php 
if (!empty($show_details)) {
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

list($oks, $errors, $warnings) = checkVersions(false);

if  ($errors) { ?>
<table class="inner" width="100%">
<tr>
	<td class="errorlong" colspan="2">
		<?php echo pluralize_n2(ngettext("One file is missing, corrupt or older than expected.", 
					"%d files are missing, corrupt or older than expected.", count($errors)), 
						count($errors), _("All files okay."));
		?>
	</td>
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
?>

</table>
<?php
}

if ($warnings) {
?>

<table class="inner" width="100%">
<tr>

	<td class="warninglong" colspan="2">
	<?php echo pluralize_n2(ngettext("One file is more recent than expected.", 
				"%d files are more recent than expected.", count($warnings)),
					count($warnings), _("All files okay.")); 
	?>
	</td>
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
?>

</table>
<?php } ?>

<table class="inner" width="100%">
<tr>
	<td class="successlong" colspan="2">
		<?php echo pluralize_n2(ngettext("One file is up-to-date.",
					"%d files are up-to-date.", count($oks)),
					count($oks),  _("All files are up-to-date."));
		?>
	</td>
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
