<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2004 Bharat Mediratta
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

require(dirname(__FILE__) . '/init.php');
doctype();
?>
<html>
<head>
  <title><?php echo _("Publishing with Windows XP") ?></title>
  <?php common_header(); ?>
</head>
<body dir="<?php echo $gallery->direction ?>">

<p class="popuphead" align="center">
<?php echo _("Using the Windows XP <i>Publish To the Web</i> feature") ?>
</p>

<span class="popup">
<?php 
	echo sprintf(_("Windows XP comes with a nice feature that allows you to publish content from your desktop directly to a web service.  %s <b>has experimental</b> support for this feature."), Gallery());
	echo _("It's relatively easy to configure.");
?>  

<br><br>

<b><?php echo _("Step 1") ?></b>
<br>
<?php 
	echo sprintf(_("Download the %sXP Configuration File%s"), 
		'<a href="'.makeGalleryUrl('publish_xp.php').'">', '</a>');
	echo sprintf(_("Save this file on your PC and rename it %s."), '"install_registry.reg"');
	echo _("If it asks you for confirmation about changing the file type, answer &quot;yes&quot;.");
	echo _("Right click on this file and you should see a menu appear.");
	echo _("Select the <b>Merge</b> option (this should be at the top of the menu).");
	echo _("It will ask you if you want to import these values into your registry.");
	echo _("Click &quot;ok&quot;.");
	echo _("It will tell you that the files were imported successfully.");
	echo _("Click &quot;ok&quot; again.");
?>
<br><br>

<b><?php echo _("Step 2") ?></b>
<br>
<?php 	echo _("Open your Windows explorer and browse to a folder containing supported images.") ."  ";
	echo _("Select the image(s) or a folder and there should be a link on the left that says &quot;Publish this file to the web...&quot;");
	echo _("Click this link and then follow the instructions to log into your Gallery, select an album and publish the image.") ?>
<br>
<br>

<center>
<a href="<?php echo makeGalleryUrl("add_photos.php") ?>"><?php echo _("Return to Add Photos") ?></a>
<center>

</span>
</body>
</html>
<?php
?>
