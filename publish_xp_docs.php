<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2002 Bharat Mediratta
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
 */
?>
<?php require($GALLERY_BASEDIR . "init.php"); ?>
<?php
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print "Security violation\n";
	exit;
}
?>

<html>
<head>
  <title>Publishing with Windows XP</title>
  <?php echo getStyleSheetLink() ?>
</head>
<body>
<center>
<span class="title">
Using the Windows XP <i>Publish To the Web</i> feature
</span>
</center>

Windows XP comes with a nice feature that allows you to publish content
from your desktop directly to a web service.  Gallery <b>has experimental</b>
support for this feature.  It's relatively easy to configure.  

<br>
<br>

<br>
<b>Step 1</b>
<br>
Download the <a href="<?php echo makeGalleryUrl("publish_xp.php")?>">XP Configuration File</a>.
Save this file on your PC and rename it "install_registry.reg".  If it asks you for
confirmation about changing the file type, answer "yes".  Right click on this file and
you should see a menu appear.  Select the <b>Merge</b> option (this should be at the top of the
menu).  It will ask you if you want to import these values into your registry.  Click "ok".
It will tell you that the files were imported successfully.  Click "ok" again.
<br>
<br>

<b>Step 2</b>
<br>
Open your Windows explorer and browse to a folder containing a JPG image.  Select the
image and there should be a link on the left that says "Publish this file to the web..."
Click this link and then follow the instructions to log into your Gallery, select an
 album and publish the image.
<br>
<br>

<center>
<a href="<?php echo makeGalleryUrl("add_photos.php")?>">Return to Add Photos</a>
<center>

<?php
?>