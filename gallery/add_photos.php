<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
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
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

require_once(dirname(__FILE__) . '/init.php');

$mode = getRequestVar('mode');

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	echo _("You are not allowed to perform this action!");
	exit;
}

$cookieName = $gallery->app->sessionVar . "_add_photos_mode";
$modeCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;

if (isset($mode)) {
	if ($modeCookie != $mode) {
	    setcookie($cookieName, $mode, time()+60*60*24*365, "/" );
	}
} else {
	if (isset($modeCookie)) {
	    $mode = $modeCookie;
	}
}
doctype();
?>

<html>
<head>
  <title><?php echo _("Add Photos") ?></title>
  <?php common_header(); ?>
  <script type="text/javascript" language="Javascript">
  <!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
  // -->
  </script>
</head>
<body dir="<?php echo $gallery->direction ?>" onload="window.focus()" class="popupbody">
<div class="popuphead"><?php echo _("Add Photos") ?></div>
<div class="popup">
<?php

if (file_exists(dirname(__FILE__) . "/java/GalleryRemoteAppletMini.jar") &&
	file_exists(dirname(__FILE__) . "/java/GalleryRemoteHTTPClient.jar")) {
    $modes["applet_mini"] = _("Applet");
	
	if (file_exists(dirname(__FILE__) . "/java/GalleryRemoteApplet.jar")) {
	    $modes["applet"] = _("Applet (big)");
	}
}

$modes["form"] = _("Form");
$modes["url"] = _("URL");
$modes["other"] = _("Other");

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = isset($modes[$gallery->app->uploadMode]) ? $gallery->app->uploadMode : "form";
}
?>

	<div id="container">
	<ul id="tabnav">
<?php
foreach ($modes as $m => $mt) {
	$url = makeGalleryUrl('add_photos.php', array('mode' => $m, 'type' => 'popup'));
	if ($m == $mode) {
		echo "\t\t<li><a href=\"$url\" class=\"active\">$mt</a></li>\n";
	} else {
		echo "\t\t<li><a href=\"$url\">$mt</a></li>\n";
	}
}
echo "\n\t</ul>";

include (dirname(__FILE__) . "/includes/add_photos/add_$mode.inc");?>
	</div>
</div>
</body>
</html>
