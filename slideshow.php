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

require(dirname(__FILE__)  . '/init.php');

list($mode) = getRequestVar(array('mode'));

$cookieName = $gallery->app->sessionVar . "_slideshow_mode";
$modeCookie = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
if (isset($mode)) {
	if ($modeCookie != $mode) {
		setcookie($cookieName, $mode, time()+60*60*24*365, "/" );
	}
}
else {
	if (isset($modeCookie)) {
		$mode = $modeCookie;
	}
}

// Hack check
if (empty($gallery->session->albumName) &&
    $gallery->app->gallery_slideshow_type == "off")
{
	header("Location: " . makeAlbumHeaderUrl());
	return;
}

$albumName = $gallery->session->albumName;

if (!empty($albumName)) {
	if (!$gallery->user->canReadAlbum($gallery->album)) {
		header("Location: " . makeAlbumHeaderUrl());
		return;
	}

	$album = $gallery->album;

	if (!$album->isLoaded()) {
		header("Location: " . makeAlbumHeaderUrl());
		return;
	}

	if ($album->fields["slideshow_type"] == "off") {
		header("Location: " . makeAlbumHeaderUrl($gallery->session->albumName));
		return;
	}
}

// common initialization
if (empty($albumName)) {
	$album		= null;
	$recursive	= true;
	$number		= (int)$gallery->app->gallery_slideshow_length;
	$random		= ($gallery->app->gallery_slideshow_type == "random");
	$loop		= ($gallery->app->gallery_slideshow_loop == "yes");
}
else {
	$recursive	= ($album->fields["slideshow_recursive"] == "yes");
	$loop		= ($album->fields["slideshow_loop"] == "yes");
	$random		= ($album->fields["slideshow_type"] == "random");
	$number		= (int)$album->fields["slideshow_length"];
	$bgcolor	= $gallery->album->fields['bgcolor'];
}

$playIconText		= getIconText('slideshow/1rightarrow.gif', gTranslate('core', "Play"));
$stopIconText		= getIconText('slideshow/play_stop.gif', gTranslate('core', "Stop"));
$normalSizeIconText	= getIconText('window_nofullscreen.gif', gTranslate('core', "Normal size"));
$fullSizeIconText	= getIconText('window_fullscreen.gif', gTranslate('core', "Full size"));
$forwardIconText	= getIconText('slideshow/1rightarrow.gif', gTranslate('core', "Forward direction"));
$backwardIconText	= getIconText('slideshow/1leftarrow.gif', gTranslate('core', "Reverse direction"));
$delayIconText		= getIcontext('history.gif', gTranslate('core', "Delay"));
$loopIconText		= getIcontext('reload.gif', gTranslate('core', "Loop:"));

// in offline mode, only high is available, because it's the only
// one where the photos can be spidered...
if (file_exists(dirname(__FILE__) . "/java/GalleryRemoteAppletMini.jar") &&
	file_exists(dirname(__FILE__) . "/java/GalleryRemoteHTTPClient.jar") &&
	! $gallery->session->offline)
{
	$modes["applet"] = gTranslate('core', "Fullscreen applet");
}

$modes["high"] = gTranslate('core', "Modern browsers");

if (!empty($albumName) && !$gallery->session->offline) {
	$modes["low"] = gTranslate('core', "Compatible but limited");
}

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = isset($modes[$gallery->app->slideshowMode]) ? $gallery->app->slideshowMode : "high";
}

include(dirname(__FILE__) . "/includes/slideshow/$mode.inc");

slideshow_initialize();

if (!$GALLERY_EMBEDDED_INSIDE) {
	doctype();
?>
<html>
<head>
  <title><?php echo $title; ?></title>
<?php
common_header();

// the link colors have to be done here to override the style sheet
if ($albumName) {
	if( !empty($gallery->album->fields["linkcolor"]) ||
	!empty($gallery->album->fields["bgcolor"]) ||
	!empty($gallery->album->fields["textcolor"]))
{
		echo "\n<style type=\"text/css\">";
		// the link colors have to be done here to override the style sheet
		if ($gallery->album->fields["linkcolor"]) {
			echo "\n  a:link, a:visited, a:active {";
			echo "\n	color: ".$gallery->album->fields['linkcolor'] ."; }";
			echo "\n  a:hover { color: #ff6600; }";

		}

		if ($gallery->album->fields["bgcolor"]) {
			echo "body { background-color:".$gallery->album->fields['bgcolor']."; }";
		}

		if (isset($gallery->album->fields['background']) && $gallery->album->fields['background']) {
			echo "body { background-image:url(".$gallery->album->fields['background']."); } ";
		}

		if ($gallery->album->fields["textcolor"]) {
			echo "body, tf {color:".$gallery->album->fields['textcolor']."; }";
			echo ".head {color:".$gallery->album->fields['textcolor']."; }";
			echo ".headbox {background-color:".$gallery->album->fields['bgcolor']."; }";
		}

		echo "\n  </style>";
	}
}
?>
</head>

<body>

<?php }

includeTemplate("slideshow.header"); ?>

<script src="<?php echo $gallery->app->photoAlbumURL ?>/js/client_sniff.js" type="text/javascript"></script>
<script type="text/javascript">
<?php
if ($mode != 'low') {
	?>
	// Browser capabilities detection ---
	// - assume only IE4+ and NAV6+ can do image resizing, others redirect to low
	if ( (is_ie && !is_ie4up) || (is_opera && !is_opera5up) || (is_nav && !is_nav6up)) {
		document.location = "<?php echo makeGalleryUrl('slideshow.php',array('mode' => 'low', "set_albumName" => $gallery->session->albumName)); ?>";
	}
	<?php
}
?>
</script>

<?php

slideshow_body();

$imageDir = $gallery->app->photoAlbumURL."/images";

#-- breadcrumb text ---
$breadcrumb["text"] = @returnToPathArray($gallery->album, true, true);

$adminbox['commands'] = '';
if (!$gallery->session->offline) {
	foreach ($modes as $m => $mt) {
		$url = makeGalleryUrl('slideshow.php',array('mode' => $m, "set_albumName" => $gallery->session->albumName));
		if ($m != $mode) {
			$adminbox['commands'] .= "&nbsp;<a href=\"$url\">[" .$modes[$m] ."]</a>";
		}
		else {
			$adminbox['commands'] .= "&nbsp;" .$modes[$m];
		}
	}
}

includeLayout('adminbox.inc');

includeLayout('breadcrumb.inc');

slideshow_controlPanel();

echo "\n<br clear=\"all\">";

slideshow_image();

echo languageSelector();

includeTemplate('overall.footer');

if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
