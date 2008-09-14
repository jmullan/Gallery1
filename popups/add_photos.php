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

require_once(dirname(dirname(__FILE__)) . '/init.php');

$mode = getRequestVar('mode');

// Hack check
if (!$gallery->user->canAddToAlbum($gallery->album)) {
	printPopupStart(gTranslate('core', "Add items"), '', 'left');
	showInvalidReqMesg(gTranslate('core', "You are not allowed to perform this action!"));
	exit;
}

$cookieName = $gallery->app->sessionVar . '_add_photos_mode';
$modeCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;

if (isset($mode)) {
	if ($modeCookie != $mode) {
		setcookie($cookieName, $mode, time()+60*60*24*365, '/' );
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

if (file_exists(dirname(dirname(__FILE__)) . '/java/GalleryRemoteAppletMini.jar') &&
	file_exists(dirname(dirname(__FILE__)) . '/java/GalleryRemoteHTTPClient.jar'))
{
	$modes['applet_mini'] = gTranslate('core', "Applet");

	if (file_exists(dirname(dirname(__FILE__)) . '/java/GalleryRemoteApplet.jar')) {
		$modes['applet'] = gTranslate('core', "Applet (big)");
	}
}

$modes['form']	= gTranslate('core', "Form");
$modes['url']	= gTranslate('core', "URL");
$modes['local']	= gTranslate('core', "From Local Server");
$modes['other']	= gTranslate('core', "Other");

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = isset($modes[$gallery->app->uploadMode]) ? $gallery->app->uploadMode : 'form';
}
?>

	<div class="g-tabset floatleft">
<?php
foreach ($modes as $m => $mt) {
	$url = makeGalleryUrl('add_photos.php', array('mode' => $m, 'type' => 'popup'));
	if ($m == $mode) {
		echo "\t\t<a href=\"$url\" class=\"g-activeTab\">$mt</a>\n";
	}
	else {
		echo "\t\t<a href=\"$url\">$mt</a>\n";
	}
}
echo "\n\t</div>";

include (dirname(dirname(__FILE__)) . "/includes/add_photos/add_$mode.inc");
?>

</div>
</body>
</html>
