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
<style type="text/css">
<!--
#container
	{
		padding: 2px;
	}

#tabnav
	{
		height: 20px;
		margin: 0;
		padding-left: 5px;
		background: url(images/tab_bottom.gif) repeat-x bottom;
	}

#tabnav li
	{
		margin: 0; 
		padding: 0;
  		display: inline;
  		list-style-type: none;
  	}
	
#tabnav a:link, #tabnav a:visited
	{
		float: left;
		font-size: 11px;
		line-height: 14px;
		font-weight: bold;
		padding: 2px 5px 2px 5px;
		margin-right: 4px;
		text-decoration: none;
		color: #666;
	        border-width:1px;
	        border-style: solid; border-color: #000000;
		-Moz-Border-Radius-TopLeft: 20px;
		-Moz-Border-Radius-TopRight: 20px;
	}

#tabnav a:link.active, #tabnav a:visited.active
	{
	  background-color: #FCFCF3 ; padding:2px 5px 2px 5px; font-size:12px;
	  margin-right: 4px;
	  border-style: solid; border-color: #000000;
	  -Moz-Border-Radius-TopLeft: 20px;
	  -Moz-Border-Radius-TopRight: 20px;
	  color:#000000;
	}

#tabnav a:hover
	{
		color: #444
	}
-->
</style>
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
// todo: this mode is broken. Fix it before enabling it again...
//$modes["form_one"] = _("Form (1)");
$modes["url"] = _("URL");
$modes["other"] = _("Other");

if ($gallery->user->isAdmin()) {
    $modes["admin"] = _("Admin");
}

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
?>
	</ul>
<?php
include (dirname(__FILE__) . "/includes/add_photos/add_$mode.inc");
?>

	</div>
</div>
</body>
</html>
