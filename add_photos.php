<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2003 Bharat Mediratta
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
// Hack prevention.
if (!empty($HTTP_GET_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_POST_VARS["GALLERY_BASEDIR"]) ||
		!empty($HTTP_COOKIE_VARS["GALLERY_BASEDIR"])) {
	print _("Security violation") ."\n";
	exit;
}
?>
<?php if (!isset($GALLERY_BASEDIR)) {
    $GALLERY_BASEDIR = './';
}
require($GALLERY_BASEDIR . 'init.php'); ?>
<?php
// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	exit;
}
	
?>

<html>
<head>
  <title><?php echo _("Add Photos") ?></title>
  <?php echo getStyleSheetLink() ?>

<script language="Javascript">
<!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
// -->
</script>
</head>
<body dir="<?php echo $gallery->direction ?>">

<?php

if (file_exists("java/GalleryRemoteAppletMini.jar") &&
	file_exists("java/GalleryRemoteHTTPClient.jar")) {
    $modes["applet_mini"] = _("Applet (mini)");
	
	if (file_exists("java/GalleryRemoteApplet.jar")) {
	    $modes["applet"] = _("Applet (window)");
	}
}


$modes["form"] = _("Form");
$modes["form_one"] = _("Form (one picture)");
$modes["url"] = _("Add from URL");
$modes["other"] = _("Other methods");

if ($gallery->user->isAdmin()) {
    $modes["admin"] = _("Admin");
}

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = key($modes);
}
?>

<table cellpadding="5" border="1">
<tr>
<?php
foreach ($modes as $m => $mt) {
	echo "<td>";
	if ($m == $mode) {
		echo "<b>$mt</b>";
	} else {
		echo "<a href=\"add_photos.php?mode=$m\">$mt</a>";
	}
	echo "</td>";
}
?>
</tr>
</table>

<?php
include "add_$mode.inc";
?>

</body>
</html>
