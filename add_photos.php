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
?><?php
// we need to turn on output buffering because all the includes leak 
// some text before we're ready to set the cookies!
ob_start();
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
<?php
$cookieName = $gallery->app->sessionVar."add_photos_mode";
$modeCookie = $HTTP_COOKIE_VARS[$cookieName];
if (isset($mode)) {
	if ($modeCookie != $mode) {
	    setcookie($cookieName, $mode, time()+60*60*24*365, "/" );
	}
} else {
	if (isset($modeCookie)) {
	    $mode = $modeCookie;
	}
}
ob_end_flush();
?>

<html>
<head>
  <title><?php echo _("Add Photos") ?></title>
  <?php echo getStyleSheetLink() ?>
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
		font-size: 10px;
		line-height: 14px;
		font-weight: bold;
		padding: 2px 5px 2px 5px;
		margin-right: 4px;
		border: 1px solid #000;
		text-decoration: none;
		color: #666;
	}

#tabnav a:link.active, #tabnav a:visited.active
	{
		border-bottom: 1px solid #fff;
		color: #000;
	}

#tabnav a:hover
	{
		color: #444
	}
-->
</style>
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
    $modes["applet_mini"] = _("Applet");
	
	if (file_exists("java/GalleryRemoteApplet.jar")) {
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
	$mode = key($modes);
}
?>

<div id="container">
<ul id="tabnav">
<?php
foreach ($modes as $m => $mt) {
	$url=makeGalleryUrl('add_photos.php',array('mode' => $m));
	echo "<td>";
	if ($m == $mode) {
		echo "<li><a href=\"$url\" class=\"active\">$mt</a></li>";
	} else {
		echo "<li><a href=\"$url\">$mt</a></li>";
	}
	echo "</td>";
}
?>
</ul>

<?php
include ($GALLERY_BASEDIR . "includes/add_photos/add_$mode.inc");
?>

</div>

</body>
</html>
