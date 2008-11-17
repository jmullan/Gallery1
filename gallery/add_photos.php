<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

/**
 * @package Add Photos
 */
require_once(dirname(__FILE__) . '/init.php');

$mode = getRequestVar('mode');

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	echo gTranslate('core', "You are not allowed to perform this action!");
	exit;
}

$curCookieParams=session_get_cookie_params(); 

$cookieName = $gallery->app->sessionVar . '_add_photos_mode';
$modeCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;

if (isset($mode)) {
	if ($modeCookie != $mode) {
		setcookie(
			$cookieName,
			$mode,
			time()+60*60*24*365,
			'/',
			$curCookieParams['domain'],
			isHttpsConnection()
		); 
	}
}
else {
	if (isset($modeCookie)) {
	    $mode = $modeCookie;
	}
}
printPopupStart(gTranslate('core', "Add items"), '', 'left');

?>

  <script type="text/javascript" language="Javascript">
  <!--
	function reloadPage() {
		document.count_form.submit();
		return false;
	}
  // -->
  </script>
<?php

if (file_exists(dirname(__FILE__) . "/java/GalleryRemoteAppletMini.jar") &&
	file_exists(dirname(__FILE__) . "/java/GalleryRemoteHTTPClient.jar")) {
    $modes["applet_mini"] = gTranslate('core', "Applet");

	if (file_exists(dirname(__FILE__) . "/java/GalleryRemoteApplet.jar")) {
	    $modes["applet"] = gTranslate('core', "Applet (big)");
	}
}

$modes['form']	= gTranslate('core', "Form");
$modes['url']	= gTranslate('core', "URL");
$modes['other']	= gTranslate('core', "Other");

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = isset($modes[$gallery->app->uploadMode]) ? $gallery->app->uploadMode : 'form';
}
?>

	<div id="container">
	<ul id="tabnav">
<?php
foreach ($modes as $m => $mt) {
	$url = makeGalleryUrl('add_photos.php', array('mode' => $m, 'type' => 'popup'));
	if ($m == $mode) {
		echo "\t\t<li><a href=\"$url\" class=\"active\">$mt</a></li>\n";
	}
	else {
		echo "\t\t<li><a href=\"$url\">$mt</a></li>\n";
	}
}
echo "\n\t</ul>";

include (dirname(__FILE__) . "/includes/add_photos/add_$mode.inc");?>
	</div>

<?php includeHtmlWrap("popup.footer"); ?>

</body>
</html>