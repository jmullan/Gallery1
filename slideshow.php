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
require($GALLERY_BASEDIR . 'init.php');

$cookieName = $gallery->app->sessionVar."slideshow_mode";
$modeCookie = isset($HTTP_COOKIE_VARS[$cookieName]) ? $HTTP_COOKIE_VARS[$cookieName] : null;
if (isset($mode)) {
	if ($modeCookie != $mode) {
	    setcookie($cookieName, $mode, time()+60*60*24*365, "/" );
	}
} else {
	if (isset($modeCookie)) {
	    $mode = $modeCookie;
	}
}
?>
<?php
// Hack check

if ($gallery->session->albumName == "" &&
	       $gallery->app->gallery_slideshow_type == "off") {
        header("Location: albums.php");
        return;
}

if (!$gallery->user->canReadAlbum($gallery->album)) {
	header("Location: " . makeAlbumUrl());
	return;
}

$albumName = $gallery->session->albumName;

if (!empty($albumName)) {
    $album = new Album();
    $album->load($albumName);
    if ($album->fields["slideshow_type"] == "off") {
        header("Location: " . makeAlbumUrl($gallery->session->albumName));
        return;
    } 

	if (!$gallery->album->isLoaded()) {
		header("Location: " . makeAlbumUrl());
		return;
	}
}
?>

<?php
// in offline mode, only high is available, because it's the only
// one where the photos can be spidered...
if (file_exists("java/GalleryRemoteAppletMini.jar") &&
	file_exists("java/GalleryRemoteHTTPClient.jar") &&
	! $gallery->session->offline) {
    $modes["applet"] = _("Fullscreen applet");
}

$modes["high"] = _("For modern browsers");

if (!empty($albumName) && !$gallery->session->offline) {
    $modes["low"] = _("Compatible but limited");
}

if (!isset($mode) || !isset($modes[$mode])) {
	$mode = key($modes);
}

// todo: on the client, prevent old browsers from using High, and remove High from the bar

include($GALLERY_BASEDIR . "includes/slideshow/$mode.inc");

slideshow_initialize();
?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
<html>
<head>
  <title><?php echo $title; ?></title>
  <?php echo getStyleSheetLink() ?>
  <style type="text/css">
<?php
// the link colors have to be done here to override the style sheet
if ($albumName) {
       	if ($gallery->album->fields["linkcolor"]) {
?>
    A:link, A:visited, A:active
      { color: <?php echo $gallery->album->fields[linkcolor] ?>; }
    A:hover
      { color: #ff6600; }
<?php
       	}
       	if ($gallery->album->fields["bgcolor"]) {
	       	echo "BODY { background-color:".$gallery->album->fields[bgcolor]."; }";
       	}
       	if (isset($gallery->album->fields["background"]) && $gallery->album->fields["background"]) {
	       	echo "BODY { background-image:url(".$gallery->album->fields['background']."); } ";
       	}
       	if ($gallery->album->fields["textcolor"]) {
	       	echo "BODY, TD {color:".$gallery->album->fields[textcolor]."; }";
	       	echo ".head {color:".$gallery->album->fields[textcolor]."; }";
	       	echo ".headbox {background-color:".$gallery->album->fields[bgcolor]."; }";
       	}
}
?>
  </style>

</head>

<body dir="<?php echo $gallery->direction ?>">

<?php } ?>

<?php includeHtmlWrap("slideshow.header"); ?>

<script language="JavaScript" SRC="<?php echo $gallery->app->photoAlbumURL ?>/js/client_sniff.js"></script>
<script language="JavaScript">
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
?>

<?php
$imageDir = $gallery->app->photoAlbumURL."/images";
$pixelImage = "<img src=\"" . getImagePath('pixel_trans.gif') . "\" width=\"1\" height=\"1\" alt=\"\">";

#-- breadcrumb text ---
$breadCount = 0;
$breadtext=array();
if ($albumName) {
if (!$gallery->session->offline
	|| isset($gallery->session->offlineAlbums[$gallery->session->albumName])) {
	$breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($gallery->session->albumName) .
      	"\">" . $gallery->album->fields['title'] . "</a>";
	$breadCount++;
}
$pAlbum = $gallery->album;
do {
  if (!strcmp($pAlbum->fields["returnto"], "no")) {
    break;
  }
  $pAlbumName = $pAlbum->fields['parentAlbumName'];
  if ($pAlbumName && (!$gallery->session->offline
  	|| isset($gallery->session->offlineAlbums[$pAlbumName]))) {
    $pAlbum = new Album();
    $pAlbum->load($pAlbumName);
    $breadtext[$breadCount] = _("Album") .": <a class=\"bread\" href=\"" . makeAlbumUrl($pAlbumName) .
      "\">" . $pAlbum->fields['title'] . "</a>";
  } elseif (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
    //-- we're at the top! ---
    $breadtext[$breadCount] = _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") .
      "\">" . $gallery->app->galleryTitle . "</a>";
  } else {
	  break;
  }
  $breadCount++;
} while ($pAlbumName);

//-- we built the array backwards, so reverse it now ---
for ($i = count($breadtext) - 1; $i >= 0; $i--) {
    $breadcrumb["text"][] = $breadtext[$i];
}
}
else {
       	if (!$gallery->session->offline || isset($gallery->session->offlineAlbums["albums.php"])) {
	       	//-- we're at the top! ---
	       	$breadcrumb["text"][$breadCount] = _("Gallery") .": <a class=\"bread\" href=\"" . makeGalleryUrl("albums.php") .
		      	"\">" . $gallery->app->galleryTitle . "</a>";
		$breadCount++;
       	}
}

$breadcrumb["bordercolor"] = $borderColor;
$breadcrumb["top"] = true;

$adminbox["commands"] = "<span class=\"admin\">";

if ( !$gallery->session->offline) {
	foreach ($modes as $m => $mt) {
		$url=makeGalleryUrl('slideshow.php',array('mode' => $m, "set_albumName" => $gallery->session->albumName));
		if ($m != $mode) {
			$adminbox["commands"] .= "&nbsp;<a class=\"admin\" href=\"$url\">[" .$modes[$m] ."]</a>";
		} else {
			$adminbox["commands"] .= "&nbsp;" .$modes[$m];
		}
	}
}

$adminbox["commands"] .= "</span>";
$adminbox["text"] = _("Slide Show");
$adminbox["bordercolor"] = $borderColor;
$adminbox["top"] = true;
includeLayout('navtablebegin.inc');
includeLayout('adminbox.inc');
includeLayout('navtablemiddle.inc');
includeLayout('breadcrumb.inc');
includeLayout('navtablemiddle.inc');

?>

<?php
	slideshow_controlPanel();
?>

<?php
    includeLayout('navtableend.inc');
?>

<br>

<?php
slideshow_image();
?>

<?php
includeLayout('ml_pulldown.inc');
includeHtmlWrap("slideshow.footer"); ?>

<?php if (!$GALLERY_EMBEDDED_INSIDE) { ?>
</body>
</html>
<?php } ?>
